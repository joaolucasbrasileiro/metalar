<?php

namespace App\Http\Requests;

use App\Support\Formatters\DocumentFormatter;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {

        $login = trim($this->input('login', ''));

        $this->merge([
            'login' => filter_var($login, FILTER_VALIDATE_EMAIL)
                ? strtolower($login)
                : DocumentFormatter::onlyNumbers($login),
        ]);
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }
}
