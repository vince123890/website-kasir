<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OpenSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_id' => 'required|exists:stores,id',
            'cash_register_id' => 'nullable|exists:cash_registers,id',
            'opening_cash' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'store_id.required' => 'Please select a store',
            'store_id.exists' => 'Selected store does not exist',
            'opening_cash.required' => 'Opening cash amount is required',
            'opening_cash.min' => 'Opening cash cannot be negative',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cashier_id' => auth()->id(),
        ]);
    }
}
