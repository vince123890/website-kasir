<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
        $userId = $this->route('user');
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId),
            ],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9\-\+\(\)\s]+$/'],
            'tenant_id' => [
                Rule::requiredIf(function () {
                    return !auth()->user()->hasRole('Administrator SaaS');
                }),
                'nullable',
                'exists:tenants,id',
            ],
            'store_id' => ['nullable', 'exists:stores,id'],
            'password' => [
                $isUpdate ? 'nullable' : 'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            ],
            'password_confirmation' => [
                $isUpdate ? 'nullable' : 'required_with:password',
            ],
            'role' => ['required', 'string', 'exists:roles,name'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
            'send_activation_email' => ['boolean'],
            'must_change_password' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, angka, dan karakter khusus.',
            'role.required' => 'Role wajib dipilih.',
            'role.exists' => 'Role tidak valid.',
            'avatar.image' => 'File harus berupa gambar.',
            'avatar.max' => 'Ukuran gambar maksimal 2MB.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set tenant_id from authenticated user if not Super Admin
        if (!$this->has('tenant_id') && auth()->user() && !auth()->user()->hasRole('Administrator SaaS')) {
            $this->merge([
                'tenant_id' => auth()->user()->tenant_id,
            ]);
        }

        // Convert checkbox values to boolean
        $this->merge([
            'is_active' => $this->has('is_active') ? (bool) $this->is_active : true,
            'send_activation_email' => $this->has('send_activation_email') ? (bool) $this->send_activation_email : false,
            'must_change_password' => $this->has('must_change_password') ? (bool) $this->must_change_password : false,
        ]);
    }
}
