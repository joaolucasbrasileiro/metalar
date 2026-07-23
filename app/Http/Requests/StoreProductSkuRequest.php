<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductSkuRequest extends FormRequest
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
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'sku' => ['required', 'string', 'max:100', 'unique:product_skus,sku'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:product_skus,barcode'],
            'unit' => ['required', 'string', 'max:20'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'height' => ['nullable', 'numeric', 'min:0'],
            'transfer_batch_quantity' => ['required', 'numeric', 'gt:0'],
            'transfer_fee_per_batch' => ['required', 'numeric', 'min:0'],
        ];
    }
}
