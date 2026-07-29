<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductSkuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('sku'))) {
            $this->merge(['sku' => strtoupper(trim($this->input('sku')))]);
        }

        if (is_string($this->input('unit'))) {
            $this->merge(['unit' => strtolower(trim($this->input('unit')))]);
        }
    }

    public function rules(): array
    {
        $productSku = $this->route('productSku');

        return [
            'product_id' => ['sometimes', 'required', 'integer', 'exists:products,id'],
            'sku' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('product_skus', 'sku')->ignore($productSku),
            ],
            'barcode' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
                Rule::unique('product_skus', 'barcode')->ignore($productSku),
            ],
            'variant_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'unit' => ['sometimes', 'required', 'string', 'max:20'],
            'weight' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'length' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'width' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'height' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'transfer_batch_quantity' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'transfer_fee_per_batch' => ['sometimes', 'required', 'numeric', 'min:0'],
        ];
    }
}
