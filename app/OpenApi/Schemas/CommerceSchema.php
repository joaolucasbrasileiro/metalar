<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'Warehouse', properties: [
    new OA\Property(property: 'id', type: 'integer', example: 1),
    new OA\Property(property: 'name', type: 'string', example: 'Metalar Matriz'),
])]
#[OA\Schema(schema: 'Shop', properties: [
    new OA\Property(property: 'id', type: 'integer', example: 1),
    new OA\Property(property: 'code', type: 'string', example: 'matriz'),
    new OA\Property(property: 'name', type: 'string', example: 'Metalar Matriz'),
    new OA\Property(property: 'phone', type: 'string', example: '71999999999'),
    new OA\Property(property: 'cnpj', type: 'string', example: '11222333000181'),
    new OA\Property(property: 'address', type: 'object'),
    new OA\Property(property: 'warehouse', ref: '#/components/schemas/Warehouse', nullable: true),
])]
#[OA\Schema(schema: 'ShopResponse', properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Shop')])]
#[OA\Schema(schema: 'ShopsResponse', properties: [
    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Shop')),
])]
#[OA\Schema(schema: 'Stock', properties: [
    new OA\Property(property: 'id', type: 'integer', example: 1),
    new OA\Property(property: 'quantity_on_hand', type: 'string', example: '10.000'),
    new OA\Property(property: 'quantity_reserved', type: 'string', example: '0.000'),
    new OA\Property(property: 'quantity_available', type: 'string', example: '10.000'),
    new OA\Property(property: 'warehouse', type: 'object'),
    new OA\Property(property: 'product_sku', type: 'object'),
])]
#[OA\Schema(schema: 'StockResponse', properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Stock')])]
#[OA\Schema(schema: 'StocksPaginatedResponse', properties: [
    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Stock')),
    new OA\Property(property: 'links', ref: '#/components/schemas/PaginationLinks'),
    new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
])]
#[OA\Schema(schema: 'StockAdjustmentRequest', required: ['quantity', 'reason'], properties: [
    new OA\Property(property: 'quantity', type: 'number', example: 10),
    new OA\Property(property: 'reason', type: 'string', example: 'Entrada inicial'),
])]
#[OA\Schema(schema: 'StockMovement', properties: [
    new OA\Property(property: 'id', type: 'integer', example: 1),
    new OA\Property(property: 'type', type: 'string', example: 'adjustment'),
    new OA\Property(property: 'quantity', type: 'string', example: '10.000'),
    new OA\Property(property: 'quantity_before', type: 'string', example: '0.000'),
    new OA\Property(property: 'quantity_after', type: 'string', example: '10.000'),
    new OA\Property(property: 'reason', type: 'string', example: 'Entrada inicial'),
    new OA\Property(property: 'stock', type: 'object'),
    new OA\Property(property: 'user', type: 'object', nullable: true),
    new OA\Property(property: 'created_at', type: 'string', nullable: true),
])]
#[OA\Schema(schema: 'StockMovementsPaginatedResponse', properties: [
    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/StockMovement')),
    new OA\Property(property: 'links', ref: '#/components/schemas/PaginationLinks'),
    new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
])]
#[OA\Schema(schema: 'ShopSkuPriceInput', required: ['price'], properties: [
    new OA\Property(property: 'price', type: 'number', example: 50),
])]
#[OA\Schema(schema: 'ShopSkuPrice', properties: [
    new OA\Property(property: 'id', type: 'integer', example: 1),
    new OA\Property(property: 'price', type: 'string', example: '50.00'),
    new OA\Property(property: 'shop', type: 'object', nullable: true),
    new OA\Property(property: 'product_sku_id', type: 'integer', example: 1),
    new OA\Property(property: 'product_sku', type: 'object', nullable: true),
    new OA\Property(property: 'promotions', type: 'array', items: new OA\Items(ref: '#/components/schemas/ShopSkuPromotion')),
])]
#[OA\Schema(schema: 'ShopSkuPriceResponse', properties: [new OA\Property(property: 'data', ref: '#/components/schemas/ShopSkuPrice')])]
#[OA\Schema(schema: 'ShopSkuPricesPaginatedResponse', properties: [
    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ShopSkuPrice')),
    new OA\Property(property: 'links', ref: '#/components/schemas/PaginationLinks'),
    new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
])]
#[OA\Schema(schema: 'ShopSkuPromotionInput', required: ['promotional_price', 'quantity_limit'], properties: [
    new OA\Property(property: 'promotional_price', type: 'number', example: 40),
    new OA\Property(property: 'quantity_limit', type: 'number', example: 5),
    new OA\Property(property: 'starts_at', type: 'string', format: 'date-time', nullable: true),
    new OA\Property(property: 'ends_at', type: 'string', format: 'date-time', nullable: true),
])]
#[OA\Schema(schema: 'ShopSkuPromotion', properties: [
    new OA\Property(property: 'id', type: 'integer', example: 1),
    new OA\Property(property: 'promotional_price', type: 'string', example: '40.00'),
    new OA\Property(property: 'quantity_limit', type: 'string', example: '5.000'),
    new OA\Property(property: 'quantity_reserved', type: 'string', example: '0.000'),
    new OA\Property(property: 'quantity_sold', type: 'string', example: '0.000'),
    new OA\Property(property: 'quantity_remaining', type: 'string', example: '5.000'),
    new OA\Property(property: 'starts_at', type: 'string', nullable: true),
    new OA\Property(property: 'ends_at', type: 'string', nullable: true),
    new OA\Property(property: 'cancelled_at', type: 'string', nullable: true),
    new OA\Property(property: 'is_active', type: 'boolean', example: true),
    new OA\Property(property: 'product_sku', type: 'object', nullable: true),
    new OA\Property(property: 'created_by', type: 'object', nullable: true),
])]
#[OA\Schema(schema: 'ShopSkuPromotionResponse', properties: [new OA\Property(property: 'data', ref: '#/components/schemas/ShopSkuPromotion')])]
#[OA\Schema(schema: 'ShopSkuPromotionsPaginatedResponse', properties: [
    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ShopSkuPromotion')),
    new OA\Property(property: 'links', ref: '#/components/schemas/PaginationLinks'),
    new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
])]
class CommerceSchema {}
