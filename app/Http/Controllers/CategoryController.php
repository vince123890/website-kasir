<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Repositories\CategoryRepository;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Exception;

class CategoryController extends Controller
{
    protected CategoryRepository $categoryRepository;
    protected CategoryService $categoryService;

    public function __construct(
        CategoryRepository $categoryRepository,
        CategoryService $categoryService
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of categories
     */
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;
        $search = $request->get('search');
        $filters = [
            'is_active' => $request->get('is_active'),
            'parent_id' => $request->get('parent_id'),
        ];

        $categories = $this->categoryRepository->getByTenant($tenantId, 15, $search, $filters);
        $parentCategories = $this->categoryRepository->getParentCategories($tenantId);

        return view('categories.index', compact('categories', 'search', 'filters', 'parentCategories'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create(): View
    {
        $tenantId = auth()->user()->tenant_id;
        $parentCategories = $this->categoryRepository->getAllActive($tenantId);

        return view('categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created category in storage
     */
    public function store(CategoryRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['tenant_id'] = auth()->user()->tenant_id;

            $category = $this->categoryService->createCategory($data);

            return redirect()
                ->route('categories.index')
                ->with('success', 'Kategori berhasil ditambahkan.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan kategori: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit(int $id): View
    {
        $tenantId = auth()->user()->tenant_id;
        $category = $this->categoryRepository->getWithProductCount($id);

        if (!$category || $category->tenant_id !== $tenantId) {
            abort(404);
        }

        // Get parent categories excluding current category and its children
        $parentCategories = $this->categoryRepository->getAllActive($tenantId)
            ->filter(function ($item) use ($id) {
                return $item->id !== $id;
            });

        return view('categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified category in storage
     */
    public function update(CategoryRequest $request, int $id): RedirectResponse
    {
        try {
            $category = $this->categoryRepository->find($id);

            if (!$category || $category->tenant_id !== auth()->user()->tenant_id) {
                abort(404);
            }

            $data = $request->validated();

            $this->categoryService->updateCategory($id, $data);

            return redirect()
                ->route('categories.index')
                ->with('success', 'Kategori berhasil diperbarui.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui kategori: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified category from storage
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        try {
            $category = $this->categoryRepository->find($id);

            if (!$category || $category->tenant_id !== auth()->user()->tenant_id) {
                abort(404);
            }

            // Get reassign category ID if provided
            $reassignToCategoryId = $request->input('reassign_to_category_id');

            $this->categoryService->deleteCategory($id, $reassignToCategoryId);

            return redirect()
                ->route('categories.index')
                ->with('success', 'Kategori berhasil dihapus.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus kategori: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete categories
     */
    public function bulkDelete(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'category_ids' => 'required|array',
                'category_ids.*' => 'exists:categories,id',
                'reassign_to_category_id' => 'nullable|exists:categories,id',
            ]);

            $categoryIds = $request->input('category_ids');
            $reassignToCategoryId = $request->input('reassign_to_category_id');

            // Verify all categories belong to current tenant
            $tenantId = auth()->user()->tenant_id;
            $validCategories = \App\Models\Category::whereIn('id', $categoryIds)
                ->where('tenant_id', $tenantId)
                ->pluck('id')
                ->toArray();

            if (count($validCategories) !== count($categoryIds)) {
                return redirect()
                    ->back()
                    ->with('error', 'Beberapa kategori tidak valid.');
            }

            $results = $this->categoryService->bulkDelete($validCategories, $reassignToCategoryId);

            $message = count($results['success']) . ' kategori berhasil dihapus.';
            if (count($results['failed']) > 0) {
                $message .= ' ' . count($results['failed']) . ' kategori gagal dihapus.';
            }

            return redirect()
                ->route('categories.index')
                ->with('success', $message);
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus kategori: ' . $e->getMessage());
        }
    }

    /**
     * Export categories to Excel/CSV
     */
    public function export(Request $request): Response
    {
        try {
            $tenantId = auth()->user()->tenant_id;
            $data = $this->categoryService->exportCategories($tenantId);

            $filename = 'categories-' . date('Y-m-d-His') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($data) {
                $file = fopen('php://output', 'w');

                // Add BOM for UTF-8
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // Add header row
                if (count($data) > 0) {
                    fputcsv($file, array_keys($data[0]));
                }

                // Add data rows
                foreach ($data as $row) {
                    fputcsv($file, $row);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal mengekspor kategori: ' . $e->getMessage());
        }
    }

    /**
     * Check if category has products (AJAX)
     */
    public function checkHasProducts(int $id): \Illuminate\Http\JsonResponse
    {
        $hasProducts = $this->categoryRepository->checkHasProducts($id);
        $category = $this->categoryRepository->getWithProductCount($id);

        return response()->json([
            'has_products' => $hasProducts,
            'products_count' => $category ? $category->products_count : 0,
        ]);
    }
}
