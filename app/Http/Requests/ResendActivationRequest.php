<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResendActivationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->has('email') ? trim(strtolower($this->email)) : $this->email,
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}
