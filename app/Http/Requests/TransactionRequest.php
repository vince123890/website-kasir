<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_id' => 'required|exists:stores,id',
            'store_session_id' => 'nullable|exists:store_sessions,id',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,transfer,ewallet,split',
            'split_payments' => 'required_if:payment_method,split|array',
            'split_payments.*.method' => 'required_if:payment_method,split|in:cash,card,transfer,ewallet',
            'split_payments.*.amount' => 'required_if:payment_method,split|numeric|min:0',
            'split_payments.*.reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'store_id.required' => 'Store is required',
            'items.required' => 'At least one item is required',
            'items.min' => 'At least one item is required',
            'items.*.product_id.required' => 'Product is required for all items',
            'items.*.product_id.exists' => 'Selected product does not exist',
            'items.*.quantity.required' => 'Quantity is required for all items',
            'items.*.quantity.min' => 'Quantity must be greater than 0',
            'paid_amount.required' => 'Paid amount is required',
            'paid_amount.min' => 'Paid amount cannot be negative',
            'payment_method.required' => 'Payment method is required',
            'payment_method.in' => 'Invalid payment method',
            'split_payments.required_if' => 'Split payments are required when using split payment method',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cashier_id' => auth()->id(),
        ]);
    }
}
