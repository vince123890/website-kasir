<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
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
        // Auto-generate slug from name if not provided (only on create)
        if ($this->isMethod('POST') && !$this->has('slug') && $this->has('name')) {
            $this->merge([
                'slug' => $this->generateSlug($this->name),
            ]);
        }

        // Convert checkbox values to boolean
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $categoryId = $this->route('category'); // For update requests
        $tenantId = auth()->user()->tenant_id;

        return [
            // Basic Information
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->where(function ($query) use ($tenantId) {
                    return $query->where('tenant_id', $tenantId);
                })->ignore($categoryId),
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                'lowercase',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('categories')->where(function ($query) use ($tenantId) {
                    return $query->where('tenant_id', $tenantId);
                })->ignore($categoryId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],

            // Hierarchy
            'parent_id' => [
                'nullable',
                'exists:categories,id',
                function ($attribute, $value, $fail) use ($categoryId) {
                    // Prevent circular reference
                    if ($value && $categoryId && $value == $categoryId) {
                        $fail('Kategori tidak boleh menjadi parent dari dirinya sendiri.');
                    }
                },
            ],

            // Tenant
            'tenant_id' => ['required', 'exists:tenants,id'],

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
            'name' => 'nama kategori',
            'slug' => 'slug',
            'description' => 'deskripsi',
            'parent_id' => 'kategori induk',
            'tenant_id' => 'tenant',
            'is_active' => 'status aktif',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max' => 'Nama kategori maksimal 255 karakter.',
            'name.unique' => 'Nama kategori sudah digunakan.',

            'slug.required' => 'Slug wajib diisi.',
            'slug.max' => 'Slug maksimal 255 karakter.',
            'slug.lowercase' => 'Slug harus dalam huruf kecil.',
            'slug.regex' => 'Slug hanya boleh mengandung huruf kecil, angka, dan tanda hubung.',
            'slug.unique' => 'Slug sudah digunakan.',

            'description.max' => 'Deskripsi maksimal 1000 karakter.',

            'parent_id.exists' => 'Kategori induk tidak ditemukan.',

            'tenant_id.required' => 'Tenant wajib dipilih.',
            'tenant_id.exists' => 'Tenant tidak ditemukan.',
        ];
    }

    /**
     * Generate slug from name
     */
    protected function generateSlug(string $name): string
    {
        return \Str::slug($name);
    }
}
