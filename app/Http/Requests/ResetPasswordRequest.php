<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
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
            'token' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                'confirmed',
                (Password::min(8))
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->letters(),
            ],
        ];
    }
}
