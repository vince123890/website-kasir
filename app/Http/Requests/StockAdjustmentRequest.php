<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_id' => ['required', 'exists:stores,id'],
            'adjustment_date' => ['required', 'date', 'before_or_equal:today'],
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', 'in:add,reduce'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'in:damaged,expired,lost,found,correction,other'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'store_id.required' => 'Toko harus dipilih.',
            'store_id.exists' => 'Toko yang dipilih tidak valid.',
            'adjustment_date.required' => 'Tanggal adjustment harus diisi.',
            'adjustment_date.date' => 'Format tanggal adjustment tidak valid.',
            'adjustment_date.before_or_equal' => 'Tanggal adjustment tidak boleh lebih dari hari ini.',
            'product_id.required' => 'Produk harus dipilih.',
            'product_id.exists' => 'Produk yang dipilih tidak valid.',
            'type.required' => 'Tipe adjustment harus dipilih.',
            'type.in' => 'Tipe adjustment tidak valid.',
            'quantity.required' => 'Jumlah harus diisi.',
            'quantity.integer' => 'Jumlah harus berupa angka.',
            'quantity.min' => 'Jumlah minimal 1.',
            'reason.required' => 'Alasan harus dipilih.',
            'reason.in' => 'Alasan tidak valid.',
            'notes.max' => 'Catatan maksimal 1000 karakter.',
        ];
    }
}
