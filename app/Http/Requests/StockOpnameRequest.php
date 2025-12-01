<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockOpnameRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware/policies
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'opname_date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.system_quantity' => ['required', 'integer', 'min:0'],
            'items.*.physical_quantity' => ['required', 'integer', 'min:0'],
            'items.*.variance_reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'opname_date.required' => 'Tanggal stock opname wajib diisi.',
            'opname_date.date' => 'Tanggal stock opname harus berupa tanggal yang valid.',
            'opname_date.before_or_equal' => 'Tanggal stock opname tidak boleh lebih dari hari ini.',

            'items.required' => 'Item stock opname wajib diisi.',
            'items.array' => 'Format item tidak valid.',
            'items.min' => 'Minimal harus ada 1 item.',

            'items.*.product_id.required' => 'Produk wajib dipilih.',
            'items.*.product_id.exists' => 'Produk tidak valid.',

            'items.*.system_quantity.required' => 'Kuantitas sistem wajib diisi.',
            'items.*.system_quantity.integer' => 'Kuantitas sistem harus berupa angka.',
            'items.*.system_quantity.min' => 'Kuantitas sistem minimal 0.',

            'items.*.physical_quantity.required' => 'Kuantitas fisik wajib diisi.',
            'items.*.physical_quantity.integer' => 'Kuantitas fisik harus berupa angka.',
            'items.*.physical_quantity.min' => 'Kuantitas fisik minimal 0.',

            'items.*.variance_reason.max' => 'Alasan variance maksimal 255 karakter.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'opname_date' => 'tanggal stock opname',
            'notes' => 'catatan',
            'items' => 'item',
            'items.*.product_id' => 'produk',
            'items.*.system_quantity' => 'kuantitas sistem',
            'items.*.physical_quantity' => 'kuantitas fisik',
            'items.*.variance_reason' => 'alasan variance',
        ];
    }
}
