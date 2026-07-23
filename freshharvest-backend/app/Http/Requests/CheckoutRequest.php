<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recipient_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:20'],
            'street' => ['required', 'string', 'max:255'],
            'suite' => ['nullable', 'string', 'max:100'],
            'city_state_zip' => ['required', 'string', 'max:150'],
            'payment_method' => ['required', 'in:qris,cod'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.in' => 'Metode pembayaran hanya QRIS atau COD.',
        ];
    }
}