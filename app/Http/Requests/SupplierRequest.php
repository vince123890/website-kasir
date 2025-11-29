<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
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
        $supplierId = $this->route('supplier');
        $tenantId = auth()->user()->tenant_id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('suppliers')->where(function ($query) use ($tenantId) {
                    return $query->where('tenant_id', $tenantId);
                })->ignore($supplierId),
            ],
            'contact_person' => [
                'required',
                'string',
                'max:255',
            ],
            'address' => [
                'required',
                'string',
                'max:500',
            ],
            'city' => [
                'nullable',
                'string',
                'max:100',
            ],
            'province' => [
                'nullable',
                'string',
                'max:100',
            ],
            'postal_code' => [
                'nullable',
                'string',
                'max:10',
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'payment_terms' => [
                'nullable',
                'string',
                'max:100',
            ],
            'tax_id' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^[0-9.\-]+$/', // Allow only numbers, dots, and dashes
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
            'name' => 'nama supplier',
            'code' => 'kode supplier',
            'contact_person' => 'nama kontak',
            'address' => 'alamat',
            'city' => 'kota',
            'province' => 'provinsi',
            'postal_code' => 'kode pos',
            'phone' => 'nomor telepon',
            'email' => 'email',
            'payment_terms' => 'syarat pembayaran',
            'tax_id' => 'NPWP',
            'is_active' => 'status',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama supplier wajib diisi.',
            'name.string' => 'Nama supplier harus berupa teks.',
            'name.max' => 'Nama supplier maksimal :max karakter.',

            'code.required' => 'Kode supplier wajib diisi.',
            'code.string' => 'Kode supplier harus berupa teks.',
            'code.max' => 'Kode supplier maksimal :max karakter.',
            'code.unique' => 'Kode supplier sudah digunakan.',

            'contact_person.required' => 'Nama kontak wajib diisi.',
            'contact_person.string' => 'Nama kontak harus berupa teks.',
            'contact_person.max' => 'Nama kontak maksimal :max karakter.',

            'address.required' => 'Alamat wajib diisi.',
            'address.string' => 'Alamat harus berupa teks.',
            'address.max' => 'Alamat maksimal :max karakter.',

            'city.string' => 'Kota harus berupa teks.',
            'city.max' => 'Kota maksimal :max karakter.',

            'province.string' => 'Provinsi harus berupa teks.',
            'province.max' => 'Provinsi maksimal :max karakter.',

            'postal_code.string' => 'Kode pos harus berupa teks.',
            'postal_code.max' => 'Kode pos maksimal :max karakter.',

            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.string' => 'Nomor telepon harus berupa teks.',
            'phone.max' => 'Nomor telepon maksimal :max karakter.',

            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal :max karakter.',

            'payment_terms.string' => 'Syarat pembayaran harus berupa teks.',
            'payment_terms.max' => 'Syarat pembayaran maksimal :max karakter.',

            'tax_id.string' => 'NPWP harus berupa teks.',
            'tax_id.max' => 'NPWP maksimal :max karakter.',
            'tax_id.regex' => 'Format NPWP tidak valid (gunakan format: XX.XXX.XXX.X-XXX.XXX).',

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
    }
}
