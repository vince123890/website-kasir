<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TenantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole('Administrator SaaS');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $tenantId = $this->route('tenant');
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'lowercase',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('tenants')->ignore($tenantId),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('tenants')->ignore($tenantId),
            ],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9\-\+\(\)\s]+$/'],
            'subscription_status' => [
                'required',
                Rule::in(['trial', 'active', 'expired', 'cancelled']),
            ],
            'trial_ends_at' => [
                'nullable',
                'date',
                'after:today',
                Rule::requiredIf(function () {
                    return $this->subscription_status === 'trial';
                }),
            ],
            'subscription_ends_at' => [
                'nullable',
                'date',
                'after:today',
            ],
            'is_active' => ['boolean'],
            'settings' => ['nullable', 'json'],

            // Auto-create owner fields
            'auto_create_owner' => ['boolean'],
            'owner_email' => [
                Rule::requiredIf(function () {
                    return $this->auto_create_owner;
                }),
                'nullable',
                'email',
                'max:255',
            ],
            'owner_name' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama tenant wajib diisi.',
            'slug.required' => 'Slug wajib diisi.',
            'slug.lowercase' => 'Slug harus huruf kecil.',
            'slug.regex' => 'Slug hanya boleh mengandung huruf kecil, angka, dan tanda hubung.',
            'slug.unique' => 'Slug sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
            'subscription_status.required' => 'Status subscription wajib dipilih.',
            'subscription_status.in' => 'Status subscription tidak valid.',
            'trial_ends_at.required' => 'Tanggal akhir trial wajib diisi untuk status trial.',
            'trial_ends_at.after' => 'Tanggal akhir trial harus setelah hari ini.',
            'subscription_ends_at.after' => 'Tanggal akhir subscription harus setelah hari ini.',
            'settings.json' => 'Format settings tidak valid (harus JSON).',
            'owner_email.required' => 'Email owner wajib diisi jika auto-create owner dicentang.',
            'owner_email.email' => 'Format email owner tidak valid.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert checkbox values to boolean
        $this->merge([
            'is_active' => $this->has('is_active') ? (bool) $this->is_active : true,
            'auto_create_owner' => $this->has('auto_create_owner') ? (bool) $this->auto_create_owner : false,
        ]);

        // Auto-generate slug from name if empty
        if (!$this->slug && $this->name) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->name),
            ]);
        }

        // Convert slug to lowercase
        if ($this->slug) {
            $this->merge([
                'slug' => strtolower($this->slug),
            ]);
        }
    }
}
