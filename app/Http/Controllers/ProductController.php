<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Repositories\ProductRepository;
use App\Repositories\CategoryRepository;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Exception;

class ProductController extends Controller
{
    protected ProductRepository $productRepository;
    protected CategoryRepository $categoryRepository;
    protected ProductService $productService;

    public function __construct(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        ProductService $productService
    ) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->productService = $productService;
    }

    /**
     * Display a listing of products
     */
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;
        $search = $request->get('search');
        $filters = [
            'category_id' => $request->get('category_id'),
            'is_active' => $request->get('is_active'),
            'stock_level' => $request->get('stock_level'),
            'sort_by' => $request->get('sort_by', 'created_at'),
            'sort_order' => $request->get('sort_order', 'desc'),
        ];

        $products = $this->productRepository->getByTenant($tenantId, 15, $search, $filters);
        $categories = $this->categoryRepository->getAllActive($tenantId);

        return view('products.index', compact('products', 'search', 'filters', 'categories'));
    }

    /**
     * Show the form for creating a new product
     */
    public function create(): View
    {
        $tenantId = auth()->user()->tenant_id;
        $categories = $this->categoryRepository->getAllActive($tenantId);

        return view('products.create', compact('categories'));
    }

    /**
     * Store a newly created product in storage
     */
    public function store(ProductRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();
            $data['tenant_id'] = auth()->user()->tenant_id;

            // Handle image upload
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image');
            }

            $product = $this->productService->createProduct($data);

            return redirect()
                ->route('products.index')
                ->with('success', 'Produk berhasil ditambahkan.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan produk: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified product
     */
    public function show(int $id): View
    {
        $tenantId = auth()->user()->tenant_id;
        $product = $this->productRepository->getWithStocks($id);

        if (!$product || $product->tenant_id !== $tenantId) {
            abort(404);
        }

        // Get statistics
        $statistics = $this->productService->getProductStatistics($id);

        // Get price history
        $priceHistory = $this->productRepository->getPriceHistory($id, 10);

        return view('products.show', compact('product', 'statistics', 'priceHistory'));
    }

    /**
     * Show the form for editing the specified product
     */
    public function edit(int $id): View
    {
        $tenantId = auth()->user()->tenant_id;
        $product = $this->productRepository->find($id);

        if (!$product || $product->tenant_id !== $tenantId) {
            abort(404);
        }

        $categories = $this->categoryRepository->getAllActive($tenantId);

        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product in storage
     */
    public function update(ProductRequest $request, int $id): RedirectResponse
    {
        try {
            $product = $this->productRepository->find($id);

            if (!$product || $product->tenant_id !== auth()->user()->tenant_id) {
                abort(404);
            }

            $data = $request->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image');
            }

            $this->productService->updateProduct($id, $data);

            return redirect()
                ->route('products.index')
                ->with('success', 'Produk berhasil diperbarui.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui produk: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified product from storage
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $product = $this->productRepository->find($id);

            if (!$product || $product->tenant_id !== auth()->user()->tenant_id) {
                abort(404);
            }

            $this->productService->deleteProduct($id);

            return redirect()
                ->route('products.index')
                ->with('success', 'Produk berhasil dihapus.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }

    /**
     * Bulk import products from Excel/CSV
     */
    public function bulkImport(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,csv|max:10240', // 10MB
            ]);

            $tenantId = auth()->user()->tenant_id;
            $file = $request->file('file');

            $results = $this->productService->bulkImportFromExcel($file, $tenantId);

            return response()->json([
                'success' => true,
                'message' => count($results['success']) . ' produk berhasil diimpor.',
                'results' => $results,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimpor produk: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk price update
     */
    public function bulkPriceUpdate(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'category_id' => 'nullable|exists:categories,id',
                'change_type' => 'required|in:increase_percent,decrease_percent,increase_fixed,decrease_fixed',
                'value' => 'required|numeric|min:0',
            ]);

            $tenantId = auth()->user()->tenant_id;
            $filters = [
                'category_id' => $request->input('category_id'),
                'is_active' => $request->input('is_active'),
            ];

            $result = $this->productService->bulkPriceUpdate(
                $tenantId,
                $filters,
                $request->input('change_type'),
                $request->input('value')
            );

            return redirect()
                ->route('products.index')
                ->with('success', $result['updated'] . ' produk berhasil diperbarui.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal memperbarui harga: ' . $e->getMessage());
        }
    }

    /**
     * Export products to Excel/CSV
     */
    public function export(Request $request): Response
    {
        try {
            $tenantId = auth()->user()->tenant_id;
            $filters = [
                'search' => $request->get('search'),
                'category_id' => $request->get('category_id'),
                'is_active' => $request->get('is_active'),
            ];

            $data = $this->productService->exportProducts($tenantId, $filters);

            $filename = 'products-' . date('Y-m-d-His') . '.csv';

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
                ->with('error', 'Gagal mengekspor produk: ' . $e->getMessage());
        }
    }

    /**
     * Download import template
     */
    public function downloadTemplate(): Response
    {
        try {
            $data = $this->productService->generateImportTemplate();

            $filename = 'products-import-template.csv';

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
                ->with('error', 'Gagal mengunduh template: ' . $e->getMessage());
        }
    }

    /**
     * Get price history for a product
     */
    public function priceHistory(int $id): View
    {
        $tenantId = auth()->user()->tenant_id;
        $product = $this->productRepository->find($id);

        if (!$product || $product->tenant_id !== $tenantId) {
            abort(404);
        }

        $priceHistory = $this->productRepository->getAllPriceHistory($id);

        return view('products.price-history', compact('product', 'priceHistory'));
    }

    /**
     * Override store-specific price
     */
    public function overrideStorePrice(Request $request, int $id): RedirectResponse
    {
        try {
            $request->validate([
                'store_id' => 'required|exists:stores,id',
                'price' => 'required|numeric|min:0',
            ]);

            $product = $this->productRepository->find($id);

            if (!$product || $product->tenant_id !== auth()->user()->tenant_id) {
                abort(404);
            }

            $this->productService->overrideStorePrice(
                $id,
                $request->input('store_id'),
                $request->input('price')
            );

            return redirect()
                ->back()
                ->with('success', 'Harga khusus toko berhasil disimpan.');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menyimpan harga khusus: ' . $e->getMessage());
        }
    }

    /**
     * Generate SKU (AJAX)
     */
    public function generateSKU(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'category_id' => 'required|exists:categories,id',
            ]);

            $tenantId = auth()->user()->tenant_id;
            $categoryId = $request->input('category_id');

            $sku = $this->productService->generateSKU($tenantId, $categoryId);

            return response()->json([
                'success' => true,
                'sku' => $sku,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if SKU is available (AJAX)
     */
    public function checkSKU(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'sku' => 'required|string',
                'product_id' => 'nullable|integer',
            ]);

            $tenantId = auth()->user()->tenant_id;
            $sku = $request->input('sku');
            $productId = $request->input('product_id');

            $isDuplicate = $this->productRepository->checkDuplicateSKU($sku, $tenantId, $productId);

            return response()->json([
                'available' => !$isDuplicate,
                'message' => $isDuplicate ? 'SKU sudah digunakan' : 'SKU tersedia',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'available' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
