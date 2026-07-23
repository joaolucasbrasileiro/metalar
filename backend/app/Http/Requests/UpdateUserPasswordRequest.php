<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class UpdateUserPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    //
    public function rules(): array
    {
        return [
            'current_password' => [
                'required',
                'string',
            ],

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

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $user = $this->route('user');

                if ($user && ! Hash::check($this->input('current_password'), $user->password)) {
                    $validator->errors()->add(
                        'current_password',
                        'A senha atual esta incorreta.'
                    );
                }

                if ($user && Hash::check($this->input('password'), $user->password)) {
                    $validator->errors()->add(
                        'password',
                        'Use uma nova senha que você não tenha usado anteriormente.'
                    );
                }
            },
        ];
    }
}
