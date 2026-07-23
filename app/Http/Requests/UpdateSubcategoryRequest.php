<?php

namespace App\Http\Requests;

use App\Models\Subcategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateSubcategoryRequest extends FormRequest
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
        $subcategory = $this->route('subcategory');
        $categoryId = $this->input('category_id', $subcategory->category_id);

        return [
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'parent_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:subcategories,id',
                Rule::notIn([$subcategory->id]),
            ],
            'name' => [
                'sometimes',
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('subcategories', 'name')
                    ->where('category_id', $categoryId)
                    ->ignore($subcategory),
            ],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->hasAny(['category_id', 'parent_id'])) {
                return;
            }

            /** @var Subcategory $subcategory */
            $subcategory = $this->route('subcategory');
            $categoryId = $this->input('category_id', $subcategory->category_id);
            $parentId = $this->exists('parent_id')
                ? $this->input('parent_id')
                : $subcategory->parent_id;

            if ($subcategory->children()->exists()) {
                if ((int) $categoryId !== (int) $subcategory->category_id) {
                    $validator->errors()->add(
                        'category_id',
                        'Uma subcategoria com filhas nao pode mudar de categoria principal.',
                    );
                }

                if ($parentId !== null) {
                    $validator->errors()->add(
                        'parent_id',
                        'Uma subcategoria com filhas deve permanecer no primeiro nivel.',
                    );
                }
            }

            if ($parentId === null) {
                return;
            }

            $parent = Subcategory::find($parentId);

            if (! $parent) {
                return;
            }

            if ((int) $parent->category_id !== (int) $categoryId) {
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
