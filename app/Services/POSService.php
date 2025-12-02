<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Product;
use App\Repositories\TransactionRepository;
use App\Repositories\PendingTransactionRepository;
use App\Repositories\ProductRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class POSService extends BaseService
{
    public function __construct(
        protected TransactionRepository $transactionRepository,
        protected PendingTransactionRepository $pendingTransactionRepository,
        protected ProductRepository $productRepository
    ) {}

    public function createTransaction(array $data): array
    {
        return $this->executeTransaction(function () use ($data) {
            if (empty($data['items']) || count($data['items']) === 0) {
                throw new Exception('Transaction must have at least one item');
            }

            foreach ($data['items'] as $item) {
                $product = $this->productRepository->findById($item['product_id']);
                if (!$product) {
                    throw new Exception("Product with ID {$item['product_id']} not found");
                }

                $available = $this->productRepository->getAvailableStock($item['product_id'], $data['store_id']);
                if ($available < $item['quantity']) {
                    throw new Exception("Insufficient stock for {$product->name}. Available: {$available}");
                }
            }

            $transactionData = [
                'tenant_id' => auth()->user()->tenant_id,
                'store_id' => $data['store_id'],
                'store_session_id' => $data['store_session_id'] ?? null,
                'cashier_id' => $data['cashier_id'],
                'transaction_number' => $this->transactionRepository->generateTransactionNumber($data['store_id']),
                'transaction_date' => now()->toDateString(),
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'subtotal' => 0,
                'discount_percentage' => $data['discount_percentage'] ?? 0,
                'discount_amount' => 0,
                'tax_percentage' => $data['tax_percentage'] ?? 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'paid_amount' => $data['paid_amount'],
                'change_amount' => 0,
                'payment_method' => $data['payment_method'],
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
            ];

            $transaction = $this->transactionRepository->create($transactionData);

            $subtotal = 0;
            foreach ($data['items'] as $itemData) {
                $product = $this->productRepository->findById($itemData['product_id']);

                $quantity = $itemData['quantity'];
                $unitPrice = $itemData['unit_price'] ?? $product->selling_price;
                $discountPercentage = $itemData['discount_percentage'] ?? 0;
                $discountAmount = ($quantity * $unitPrice * $discountPercentage) / 100;
                $itemSubtotal = ($quantity * $unitPrice) - $discountAmount;

                $transaction->items()->create([
                    'product_id' => $itemData['product_id'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_percentage' => $discountPercentage,
                    'discount_amount' => $discountAmount,
                    'subtotal' => $itemSubtotal,
                ]);

                $subtotal += $itemSubtotal;

                $this->productRepository->reduceStock($itemData['product_id'], $data['store_id'], $quantity);
            }

            $discountAmount = ($subtotal * $transactionData['discount_percentage']) / 100;
            $afterDiscount = $subtotal - $discountAmount;
            $taxAmount = ($afterDiscount * $transactionData['tax_percentage']) / 100;
            $totalAmount = $afterDiscount + $taxAmount;
            $changeAmount = $transactionData['paid_amount'] - $totalAmount;

            $transaction->update([
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'change_amount' => $changeAmount,
            ]);

            if ($data['payment_method'] === 'split' && !empty($data['split_payments'])) {
                foreach ($data['split_payments'] as $payment) {
                    $transaction->payments()->create([
                        'payment_method' => $payment['method'],
                        'amount' => $payment['amount'],
                        'reference_number' => $payment['reference_number'] ?? null,
                        'notes' => $payment['notes'] ?? null,
                    ]);
                }
            }

            return $this->successResponse('Transaction completed successfully', $transaction->fresh());
        });
    }

    public function holdTransaction(array $data): array
    {
        return $this->executeTransaction(function () use ($data) {
            $holdNumber = $this->pendingTransactionRepository->generateHoldNumber($data['store_id']);

            $pendingTransaction = $this->pendingTransactionRepository->create([
                'tenant_id' => auth()->user()->tenant_id,
                'store_id' => $data['store_id'],
                'cashier_id' => $data['cashier_id'],
                'hold_number' => $holdNumber,
                'transaction_data' => $data,
                'held_at' => now(),
            ]);

            return $this->successResponse('Transaction held successfully', $pendingTransaction);
        });
    }

    public function resumeTransaction(int $pendingTransactionId): array
    {
        $pendingTransaction = $this->pendingTransactionRepository->findById($pendingTransactionId);

        if (!$pendingTransaction) {
            throw new Exception('Pending transaction not found');
        }

        return [
            'success' => true,
            'data' => $pendingTransaction->transaction_data,
        ];
    }

    public function deletePendingTransaction(int $pendingTransactionId): array
    {
        return $this->executeTransaction(function () use ($pendingTransactionId) {
            $pendingTransaction = $this->pendingTransactionRepository->findById($pendingTransactionId);

            if (!$pendingTransaction) {
                throw new Exception('Pending transaction not found');
            }

            $this->pendingTransactionRepository->delete($pendingTransaction);

            return $this->successResponse('Pending transaction deleted successfully');
        });
    }

    public function voidTransaction(int $transactionId, array $data): array
    {
        return $this->executeTransaction(function () use ($transactionId, $data) {
            $transaction = $this->transactionRepository->findById($transactionId);

            if (!$transaction) {
                throw new Exception('Transaction not found');
            }

            if ($transaction->status === 'voided') {
                throw new Exception('Transaction is already voided');
            }

            foreach ($transaction->items as $item) {
                $this->productRepository->increaseStock(
                    $item->product_id,
                    $transaction->store_id,
                    $item->quantity
                );
            }

            $transaction->update([
                'status' => 'voided',
                'voided_by' => auth()->id(),
                'voided_at' => now(),
                'void_reason' => $data['void_reason'],
            ]);

            return $this->successResponse('Transaction voided successfully', $transaction->fresh());
        });
    }

    public function getProductByBarcode(string $barcode, int $storeId): ?Product
    {
        return Product::where('barcode', $barcode)
            ->whereHas('stocks', function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })
            ->first();
    }
}
