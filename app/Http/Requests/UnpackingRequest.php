<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnpackingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_id' => ['required', 'exists:stores,id'],
            'unpacking_date' => ['required', 'date', 'before_or_equal:today'],
            'source_product_id' => ['required', 'exists:products,id', 'different:result_product_id'],
            'source_quantity' => ['required', 'integer', 'min:1'],
            'result_product_id' => ['required', 'exists:products,id', 'different:source_product_id'],
            'result_quantity' => ['required', 'integer', 'min:1'],
            'conversion_ratio' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'store_id.required' => 'Toko harus dipilih.',
            'store_id.exists' => 'Toko yang dipilih tidak valid.',
            'unpacking_date.required' => 'Tanggal unpacking harus diisi.',
            'unpacking_date.date' => 'Format tanggal unpacking tidak valid.',
            'unpacking_date.before_or_equal' => 'Tanggal unpacking tidak boleh lebih dari hari ini.',
            'source_product_id.required' => 'Produk sumber harus dipilih.',
            'source_product_id.exists' => 'Produk sumber yang dipilih tidak valid.',
            'source_product_id.different' => 'Produk sumber harus berbeda dengan produk hasil.',
            'source_quantity.required' => 'Jumlah sumber harus diisi.',
            'source_quantity.integer' => 'Jumlah sumber harus berupa angka.',
            'source_quantity.min' => 'Jumlah sumber minimal 1.',
            'result_product_id.required' => 'Produk hasil harus dipilih.',
            'result_product_id.exists' => 'Produk hasil yang dipilih tidak valid.',
            'result_product_id.different' => 'Produk hasil harus berbeda dengan produk sumber.',
            'result_quantity.required' => 'Jumlah hasil harus diisi.',
            'result_quantity.integer' => 'Jumlah hasil harus berupa angka.',
            'result_quantity.min' => 'Jumlah hasil minimal 1.',
            'conversion_ratio.required' => 'Rasio konversi harus diisi.',
            'conversion_ratio.numeric' => 'Rasio konversi harus berupa angka.',
            'conversion_ratio.min' => 'Rasio konversi minimal 0.01.',
            'notes.max' => 'Catatan maksimal 1000 karakter.',
        ];
    }
}
