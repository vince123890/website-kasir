<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloseSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'actual_cash' => 'required|numeric|min:0',
            'variance_reason' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'actual_cash.required' => 'Actual cash amount is required',
            'actual_cash.min' => 'Actual cash cannot be negative',
        ];
    }
}
