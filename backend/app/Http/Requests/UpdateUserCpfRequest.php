<?php

namespace App\Http\Requests;

use App\Rules\ValidCpf;
use App\Support\Formatters\CpfFormatter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserCpfRequest extends FormRequest
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
        if ($this->has('cpf')) {
            $this->merge([
                'cpf' => CpfFormatter::onlyNumbers($this->input('cpf')),
            ]);
        }
    }

    //
    public function rules(): array
    {

        $user = $this->route('user');

        return [
            'cpf' => [
                'required',
                'string',
                new ValidCpf,
                Rule::unique('users', 'cpf')->ignore($user->id),
            ],
        ];
    }
}
