<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['required', 'date', 'after:order_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'supplier_id' => 'supplier',
            'order_date' => 'order date',
            'expected_delivery_date' => 'expected delivery date',
            'notes' => 'notes',
            'tax_amount' => 'tax amount',
            'items' => 'items',
            'items.*.product_id' => 'product',
            'items.*.quantity' => 'quantity',
            'items.*.unit_price' => 'unit price',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Supplier wajib dipilih.',
            'supplier_id.exists' => 'Supplier tidak valid.',
            'order_date.required' => 'Tanggal order wajib diisi.',
            'order_date.date' => 'Format tanggal order tidak valid.',
            'expected_delivery_date.required' => 'Tanggal pengiriman wajib diisi.',
            'expected_delivery_date.date' => 'Format tanggal pengiriman tidak valid.',
            'expected_delivery_date.after' => 'Tanggal pengiriman harus setelah tanggal order.',
            'items.required' => 'Minimal 1 item produk harus ditambahkan.',
            'items.min' => 'Minimal 1 item produk harus ditambahkan.',
            'items.*.product_id.required' => 'Produk wajib dipilih.',
            'items.*.product_id.exists' => 'Produk tidak valid.',
            'items.*.quantity.required' => 'Kuantitas wajib diisi.',
            'items.*.quantity.integer' => 'Kuantitas harus berupa angka.',
            'items.*.quantity.min' => 'Kuantitas minimal 1.',
            'items.*.unit_price.required' => 'Harga satuan wajib diisi.',
            'items.*.unit_price.numeric' => 'Harga satuan harus berupa angka.',
            'items.*.unit_price.min' => 'Harga satuan minimal 0.',
        ];
    }
}
