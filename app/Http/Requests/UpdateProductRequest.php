<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
        if (is_string($this->input('name'))) {
            $this->merge(['name' => trim($this->input('name'))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'brand_id' => ['sometimes', 'nullable', 'integer', 'exists:brands,id'],
            'subcategory_ids' => ['sometimes', 'nullable', 'array'],
            'subcategory_ids.*' => ['integer', 'distinct', 'exists:subcategories,id'],
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'specifications' => ['sometimes', 'nullable', 'array'],
            'specifications.*' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
