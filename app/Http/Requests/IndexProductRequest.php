<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['nullable', 'string', 'exists:categories,slug'],
            'subcategory' => ['nullable', 'string', 'exists:subcategories,slug'],
            'brand' => ['nullable', 'string', 'exists:brands,slug'],
            'search' => ['nullable', 'string', 'max:255'],
            'in_stock' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'in:best_offer,name'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:24'],
        ];
    }
}
