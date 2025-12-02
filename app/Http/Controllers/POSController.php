<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Services\POSService;
use App\Services\StoreSessionService;
use App\Repositories\TransactionRepository;
use App\Repositories\PendingTransactionRepository;
use App\Repositories\ProductRepository;
use Illuminate\Http\Request;
use Exception;

class POSController extends Controller
{
    public function __construct(
        protected POSService $posService,
        protected StoreSessionService $storeSessionService,
        protected TransactionRepository $transactionRepository,
        protected PendingTransactionRepository $pendingTransactionRepository,
        protected ProductRepository $productRepository
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $currentSession = $this->storeSessionService->getCurrentSession(
            auth()->id(),
            auth()->user()->store_id
        );

        if (!$currentSession) {
            return redirect()->route('sessions.create')
                ->with('warning', 'Please open a session first before using POS.');
        }

        $products = $this->productRepository->getAvailableForStore(auth()->user()->store_id);
        $pendingTransactions = $this->pendingTransactionRepository->getAllForCashier(
            auth()->id(),
            auth()->user()->store_id
        );

        return view('pos.index', compact('currentSession', 'products', 'pendingTransactions'));
    }

    public function store(TransactionRequest $request)
    {
        try {
            $result = $this->posService->createTransaction($request->validated());

            if ($request->ajax()) {
                return response()->json($result);
            }

            return redirect()->route('pos.receipt', $result['data']->id)
                ->with('success', $result['message']);
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function hold(Request $request)
    {
        try {
            $request->validate([
                'store_id' => 'required|exists:stores,id',
                'items' => 'required|array|min:1',
            ]);

            $data = $request->all();
            $data['cashier_id'] = auth()->id();

            $result = $this->posService->holdTransaction($data);

            if ($request->ajax()) {
                return response()->json($result);
            }

            return redirect()->route('pos.index')
                ->with('success', $result['message']);
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function resume($id)
    {
        try {
            $result = $this->posService->resumeTransaction($id);

            if (request()->ajax()) {
                return response()->json($result);
            }

            return redirect()->route('pos.index')
                ->with('transaction_data', $result['data']);
        } catch (Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->route('pos.index')
                ->with('error', $e->getMessage());
        }
    }

    public function deletePending($id)
    {
        try {
            $result = $this->posService->deletePendingTransaction($id);

            if (request()->ajax()) {
                return response()->json($result);
            }

            return redirect()->route('pos.index')
                ->with('success', $result['message']);
        } catch (Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->route('pos.index')
                ->with('error', $e->getMessage());
        }
    }

    public function void($id, Request $request)
    {
        try {
            $request->validate([
                'void_reason' => 'required|string|max:500',
            ]);

            $result = $this->posService->voidTransaction($id, $request->all());

            if ($request->ajax()) {
                return response()->json($result);
            }

            return redirect()->route('pos.index')
                ->with('success', $result['message']);
        } catch (Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function receipt($id)
    {
        try {
            $transaction = $this->transactionRepository->findById($id);

            if (!$transaction) {
                return redirect()->route('pos.index')
                    ->with('error', 'Transaction not found');
            }

            return view('pos.receipt', compact('transaction'));
        } catch (Exception $e) {
            return redirect()->route('pos.index')
                ->with('error', $e->getMessage());
        }
    }

    public function searchProduct(Request $request)
    {
        $search = $request->get('q');
        $barcode = $request->get('barcode');
        $storeId = $request->get('store_id', auth()->user()->store_id);

        if ($barcode) {
            $product = $this->posService->getProductByBarcode($barcode, $storeId);

            if ($product) {
                $available = $this->productRepository->getAvailableStock($product->id, $storeId);
                $product->available_stock = $available;

                return response()->json([
                    'success' => true,
                    'product' => $product,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $products = $this->productRepository->searchForStore($search, $storeId, 10);

        return response()->json([
            'success' => true,
            'products' => $products,
        ]);
    }
}
