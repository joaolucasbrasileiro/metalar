<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

use App\Rules\ValidCpf;
use App\Support\Formatters\CpfFormatter;
use App\Support\Formatters\PhoneFormatter;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->has('name') ? trim($this->name) : $this->name,
            'email' => $this->has('email') ? trim(strtolower($this->email)): $this->email,
            'cpf' => $this->cpf ? CpfFormatter::onlyNumbers($this->cpf) : null,
            'phone' => $this->phone ? PhoneFormatter::onlyNumbers($this->phone) : null,
        ]);
    }

    //
    public function rules(): array
    {
        return [
        'name' => ['required', 'string', 'min:3', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email'],

        'password' => [
            'required',
            'string',
            (Password::min(8))
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->letters()
            ],

        'birthday' => ['nullable', 'date_format:Y-m-d', 'before:today'],
        'cpf' => ['required', 'string', new ValidCpf(), 'unique:users,cpf'],
        'phone' => ['required', 'string', 'regex:/^\d{11}$/', 'unique:users,phone'],
    ];
    }
}
