<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('name'))) {
            $this->merge(['name' => trim($this->input('name'))]);
        }
    }

    public function rules(): array
    {
        return [
            'parent_id' => ['prohibited'],
            'name' => ['required', 'string', 'min:2', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
