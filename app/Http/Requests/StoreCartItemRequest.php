<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_sku_id' => ['required', 'integer', 'exists:product_skus,id'],
            'shop_id' => ['required', 'integer', 'exists:shops,id'],
            'promotion_id' => ['nullable', 'integer', 'exists:shop_sku_promotions,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
        ];
    }
}
