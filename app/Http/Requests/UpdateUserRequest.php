<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use App\Rules\ValidCpf;
use App\Support\Formatters\CpfFormatter;
use App\Support\Formatters\PhoneFormatter;

class UpdateUserRequest extends FormRequest
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
        $data = [];

        if ($this->has('name')) {
            $data['name'] = trim($this->input('name'));
        }

        if ($this->has('email')) {
            $data['email'] = strtolower(trim($this->input('email')));
        }

        if ($this->has('phone')) {
            $data['phone'] = PhoneFormatter::onlyNumbers($this->input('phone'));
        }

        $this->merge($data);
    }

    //
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'min:3',
                'max:255',
            ],

            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],

            'birthday' => [
                'sometimes',
                'nullable',
                'date_format:Y-m-d',
                'before:today',
            ],

            'phone' => [
                'sometimes',
                'required',
                'string',
                'regex:/^\d{11}$/',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
        ];
    }
}