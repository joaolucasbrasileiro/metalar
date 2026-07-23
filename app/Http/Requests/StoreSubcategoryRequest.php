<?php

namespace App\Http\Requests;

use App\Models\Subcategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSubcategoryRequest extends FormRequest
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
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'parent_id' => ['nullable', 'integer', 'exists:subcategories,id'],
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('subcategories', 'name')
                    ->where('category_id', $this->input('category_id')),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->hasAny(['category_id', 'parent_id'])) {
                return;
            }

            $parentId = $this->input('parent_id');

            if (! $parentId) {
                return;
            }

            $parent = Subcategory::find($parentId);

            if (! $parent) {
                return;
            }

            if ((int) $parent->category_id !== (int) $this->input('category_id')) {
                $validator->errors()->add(
                    'parent_id',
                    'A subcategoria pai deve pertencer a mesma categoria principal.',
                );
            }

            if ($parent->parent_id !== null) {
                $validator->errors()->add(
                    'parent_id',
                    'Uma subcategoria de segundo nivel nao pode receber outras subcategorias.',
                );
            }
        });
    }
}
