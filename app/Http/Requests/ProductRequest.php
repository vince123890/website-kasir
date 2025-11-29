<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('product');
        $tenantId = auth()->user()->tenant_id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products')->where(function ($query) use ($tenantId) {
                    return $query->where('tenant_id', $tenantId);
                })->ignore($productId),
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:100',
            ],
            'category_id' => [
                'required',
                'exists:categories,id',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'unit' => [
                'required',
                'string',
                'in:pcs,box,carton,kg,gram,liter,ml,dozen,pack,bottle,can,unit',
            ],
            'purchase_price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'selling_price' => [
                'required',
                'numeric',
                'min:0',
                'gt:purchase_price',
            ],
            'min_stock' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'max_stock' => [
                'nullable',
                'integer',
                'min:0',
                function ($attribute, $value, $fail) {
                    $minStock = $this->input('min_stock');
                    if ($minStock && $value && $value <= $minStock) {
                        $fail('Stok maksimal harus lebih besar dari stok minimal.');
                    }
                },
            ],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120', // 5MB
            ],
            'is_active' => [
                'boolean',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama produk',
            'sku' => 'SKU',
            'barcode' => 'barcode',
            'category_id' => 'kategori',
            'description' => 'deskripsi',
            'unit' => 'satuan',
            'purchase_price' => 'harga beli',
            'selling_price' => 'harga jual',
            'min_stock' => 'stok minimal',
            'max_stock' => 'stok maksimal',
            'image' => 'gambar',
            'is_active' => 'status',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama produk wajib diisi.',
            'name.string' => 'Nama produk harus berupa teks.',
            'name.max' => 'Nama produk maksimal :max karakter.',

            'sku.required' => 'SKU wajib diisi.',
            'sku.string' => 'SKU harus berupa teks.',
            'sku.max' => 'SKU maksimal :max karakter.',
            'sku.unique' => 'SKU sudah digunakan.',

            'barcode.string' => 'Barcode harus berupa teks.',
            'barcode.max' => 'Barcode maksimal :max karakter.',

            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak valid.',

            'description.string' => 'Deskripsi harus berupa teks.',
            'description.max' => 'Deskripsi maksimal :max karakter.',

            'unit.required' => 'Satuan wajib dipilih.',
            'unit.in' => 'Satuan tidak valid.',

            'purchase_price.required' => 'Harga beli wajib diisi.',
            'purchase_price.numeric' => 'Harga beli harus berupa angka.',
            'purchase_price.min' => 'Harga beli minimal :min.',

            'selling_price.required' => 'Harga jual wajib diisi.',
            'selling_price.numeric' => 'Harga jual harus berupa angka.',
            'selling_price.min' => 'Harga jual minimal :min.',
            'selling_price.gt' => 'Harga jual harus lebih besar dari harga beli.',

            'min_stock.integer' => 'Stok minimal harus berupa angka bulat.',
            'min_stock.min' => 'Stok minimal tidak boleh negatif.',

            'max_stock.integer' => 'Stok maksimal harus berupa angka bulat.',
            'max_stock.min' => 'Stok maksimal tidak boleh negatif.',

            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Gambar harus berformat: jpg, jpeg, png, atau webp.',
            'image.max' => 'Ukuran gambar maksimal :max KB (5MB).',

            'is_active.boolean' => 'Status harus berupa true atau false.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert is_active to boolean
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        // Ensure numeric values
        if ($this->has('purchase_price')) {
            $this->merge([
                'purchase_price' => str_replace(',', '', $this->purchase_price),
            ]);
        }

        if ($this->has('selling_price')) {
            $this->merge([
                'selling_price' => str_replace(',', '', $this->selling_price),
            ]);
        }
    }
}
