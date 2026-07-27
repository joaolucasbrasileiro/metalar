<?php

namespace App\Http\Requests;

use App\Enums\PersonType;
use App\Rules\ValidCnpj;
use App\Rules\ValidCpf;
use App\Support\Formatters\CnpjFormatter;
use App\Support\Formatters\CpfFormatter;
use App\Support\Formatters\PhoneFormatter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
            'email' => $this->has('email') ? trim(strtolower($this->email)) : $this->email,
            'cpf' => $this->cpf ? CpfFormatter::onlyNumbers($this->cpf) : null,
            'cnpj' => $this->cnpj ? CnpjFormatter::onlyNumbers($this->cnpj) : null,
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
                    ->letters(),
            ],

            'birthday' => ['nullable', 'date_format:Y-m-d', 'before:today'],
            'phone' => ['required', 'string', 'regex:/^\d{11}$/', 'unique:users,phone'],
            'rules' => ['accepted'],

            'person_type' => [
                'required',
                Rule::in([PersonType::INDIVIDUAL->value, PersonType::COMPANY->value]),
            ],

            'cpf' => [
                Rule::requiredIf($this->input('person_type') === PersonType::INDIVIDUAL->value),
                Rule::prohibitedIf($this->input('person_type') === PersonType::COMPANY->value),
                'nullable',
                'string',
                new ValidCpf,
                'unique:users,cpf',
            ],

            'cnpj' => [
                Rule::prohibitedIf($this->input('person_type') === PersonType::INDIVIDUAL->value),
                Rule::requiredIf($this->input('person_type') === PersonType::COMPANY->value),
                'nullable',
                'string',
                new ValidCnpj,
                'unique:users,cnpj',
            ],

        ];
    }

    public function messages(): array
    {
        return [
            'rules.accepted' => 'Aceite os Termos de Uso e a Politica de Privacidade para continuar.',
        ];
    }
}
