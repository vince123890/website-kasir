<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-generate code from name if not provided (only on create)
        if ($this->isMethod('POST') && !$this->has('code') && $this->has('name')) {
            $this->merge([
                'code' => $this->generateCode($this->name),
            ]);
        }

        // Convert checkbox values to boolean
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        if ($this->has('tax_included')) {
            $this->merge([
                'tax_included' => filter_var($this->tax_included, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $storeId = $this->route('store'); // For update requests

        return [
            // Basic Information
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                'uppercase',
                'regex:/^[A-Z0-9\-]+$/',
                Rule::unique('stores')->where(function ($query) {
                    return $query->where('tenant_id', $this->tenant_id ?? auth()->user()->tenant_id);
                })->ignore($storeId),
            ],
            'tenant_id' => ['required', 'exists:tenants,id'],

            // Address Information
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10', 'regex:/^[0-9]+$/'],

            // Contact Information
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9\-\+\(\)\s]+$/'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('stores')->where(function ($query) {
                    return $query->where('tenant_id', $this->tenant_id ?? auth()->user()->tenant_id);
                })->ignore($storeId),
            ],

            // Settings
            'timezone' => ['nullable', 'string', 'timezone'],
            'currency' => ['nullable', 'string', 'size:3'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_included' => ['nullable', 'boolean'],
            'rounding_method' => ['nullable', 'string', Rule::in([
                'none',
                'round_up',
                'round_down',
                'round_nearest',
                'round_nearest_5',
                'round_nearest_10',
                'round_nearest_100',
                'round_nearest_1000',
            ])],

            // Receipt Settings
            'receipt_header' => ['nullable', 'string', 'max:1000'],
            'receipt_footer' => ['nullable', 'string', 'max:1000'],

            // Operating Hours (JSON format)
            'operating_hours' => ['nullable', 'json'],

            // Logo
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],

            // Status
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama toko',
            'code' => 'kode toko',
            'tenant_id' => 'tenant',
            'address' => 'alamat',
            'city' => 'kota',
            'province' => 'provinsi',
            'postal_code' => 'kode pos',
            'phone' => 'nomor telepon',
            'email' => 'alamat email',
            'timezone' => 'zona waktu',
            'currency' => 'mata uang',
            'tax_rate' => 'tarif pajak',
            'tax_included' => 'pajak termasuk',
            'rounding_method' => 'metode pembulatan',
            'receipt_header' => 'header struk',
            'receipt_footer' => 'footer struk',
            'operating_hours' => 'jam operasional',
            'logo' => 'logo toko',
            'is_active' => 'status aktif',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama toko wajib diisi.',
            'name.max' => 'Nama toko maksimal 255 karakter.',

            'code.required' => 'Kode toko wajib diisi.',
            'code.max' => 'Kode toko maksimal 50 karakter.',
            'code.uppercase' => 'Kode toko harus dalam huruf kapital.',
            'code.regex' => 'Kode toko hanya boleh mengandung huruf kapital, angka, dan tanda hubung.',
            'code.unique' => 'Kode toko sudah digunakan.',

            'tenant_id.required' => 'Tenant wajib dipilih.',
            'tenant_id.exists' => 'Tenant tidak ditemukan.',

            'address.required' => 'Alamat wajib diisi.',
            'address.max' => 'Alamat maksimal 500 karakter.',

            'city.required' => 'Kota wajib diisi.',
            'city.max' => 'Kota maksimal 100 karakter.',

            'province.required' => 'Provinsi wajib diisi.',
            'province.max' => 'Provinsi maksimal 100 karakter.',

            'postal_code.regex' => 'Kode pos hanya boleh mengandung angka.',
            'postal_code.max' => 'Kode pos maksimal 10 karakter.',

            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'phone.regex' => 'Format nomor telepon tidak valid.',

            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh toko lain.',

            'timezone.timezone' => 'Zona waktu tidak valid.',

            'currency.size' => 'Kode mata uang harus 3 karakter (contoh: IDR, USD).',

            'tax_rate.numeric' => 'Tarif pajak harus berupa angka.',
            'tax_rate.min' => 'Tarif pajak minimal 0%.',
            'tax_rate.max' => 'Tarif pajak maksimal 100%.',

            'rounding_method.in' => 'Metode pembulatan tidak valid.',

            'receipt_header.max' => 'Header struk maksimal 1000 karakter.',
            'receipt_footer.max' => 'Footer struk maksimal 1000 karakter.',

            'operating_hours.json' => 'Format jam operasional tidak valid.',

            'logo.image' => 'File logo harus berupa gambar.',
            'logo.mimes' => 'Logo harus berformat JPEG, JPG, atau PNG.',
            'logo.max' => 'Ukuran logo maksimal 2MB.',
        ];
    }

    /**
     * Generate store code from name
     */
    protected function generateCode(string $name): string
    {
        return strtoupper(
            preg_replace('/[^A-Z0-9]+/', '-', strtoupper($name))
        );
    }
}
