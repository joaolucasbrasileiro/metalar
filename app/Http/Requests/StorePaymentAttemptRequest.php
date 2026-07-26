<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'method' => ['required', Rule::in(['pix', 'card'])],
            'installments' => ['nullable', 'integer', 'min:1', 'max:12'],
            'customer' => ['nullable', 'array'],
            'customer.name' => ['nullable', 'string', 'max:255'],
            'customer.email' => ['nullable', 'email', 'max:255'],
            'customer.tax_id' => ['nullable', 'string', 'max:20'],
            'customer.cellphone' => ['nullable', 'string', 'max:30'],
            'card' => ['required_if:method,card', 'array'],
            'card.holder_name' => ['required_if:method,card', 'string', 'max:255'],
            'card.number' => ['required_if:method,card', 'string', 'max:25'],
            'card.expiration_month' => ['required_if:method,card', 'integer', 'min:1', 'max:12'],
            'card.expiration_year' => ['required_if:method,card', 'integer', 'min:2026', 'max:2100'],
            'card.cvv' => ['required_if:method,card', 'string', 'min:3', 'max:4'],
        ];
    }
}
