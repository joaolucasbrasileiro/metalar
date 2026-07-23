<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'BrandInput', required: ['name'], properties: [
    new OA\Property(property: 'name', type: 'string', example: 'Votorantim'),
    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Fabricante de materiais de construcao.'),
])]
#[OA\Schema(schema: 'Brand', properties: [
    new OA\Property(property: 'id', type: 'integer', example: 1),
    new OA\Property(property: 'name', type: 'string', example: 'Votorantim'),
    new OA\Property(property: 'slug', type: 'string', example: 'votorantim'),
    new OA\Property(property: 'description', type: 'string', nullable: true),
    new OA\Property(property: 'logo_url', type: 'string', nullable: true),
    new OA\Property(property: 'products_count', type: 'integer', nullable: true, example: 12),
])]
#[OA\Schema(schema: 'BrandResponse', properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Brand')])]
#[OA\Schema(schema: 'BrandsPaginatedResponse', properties: [
    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Brand')),
    new OA\Property(property: 'links', ref: '#/components/schemas/PaginationLinks'),
    new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
])]
#[OA\Schema(schema: 'CategoryInput', required: ['name'], properties: [
    new OA\Property(property: 'name', type: 'string', example: 'Cimentos'),
    new OA\Property(property: 'description', type: 'string', nullable: true),
])]
#[OA\Schema(schema: 'Category', properties: [
    new OA\Property(property: 'id', type: 'integer', example: 1),
    new OA\Property(property: 'name', type: 'string', example: 'Cimentos'),
    new OA\Property(property: 'slug', type: 'string', example: 'cimentos'),
    new OA\Property(property: 'description', type: 'string', nullable: true),
    new OA\Property(property: 'subcategories_count', type: 'integer', nullable: true, example: 4),
])]
#[OA\Schema(schema: 'CategoryResponse', properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Category')])]
#[OA\Schema(schema: 'CategoriesPaginatedResponse', properties: [
    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Category')),
    new OA\Property(property: 'links', ref: '#/components/schemas/PaginationLinks'),
    new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
])]
#[OA\Schema(schema: 'SubcategoryInput', required: ['category_id', 'name'], properties: [
    new OA\Property(property: 'category_id', type: 'integer', example: 1),
    new OA\Property(property: 'parent_id', type: 'integer', nullable: true, example: null),
    new OA\Property(property: 'name', type: 'string', example: 'Cimento CP II'),
    new OA\Property(property: 'description', type: 'string', nullable: true),
])]
#[OA\Schema(schema: 'Subcategory', properties: [
    new OA\Property(property: 'id', type: 'integer', example: 1),
    new OA\Property(property: 'name', type: 'string', example: 'Cimento CP II'),
    new OA\Property(property: 'slug', type: 'string', example: 'cimento-cp-ii'),
    new OA\Property(property: 'description', type: 'string', nullable: true),
    new OA\Property(property: 'level', type: 'integer', example: 1),
    new OA\Property(property: 'children_count', type: 'integer', nullable: true, example: 2),
    new OA\Property(property: 'products_count', type: 'integer', nullable: true, example: 8),
])]
#[OA\Schema(schema: 'SubcategoryResponse', properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Subcategory')])]
#[OA\Schema(schema: 'SubcategoriesPaginatedResponse', properties: [
    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Subcategory')),
    new OA\Property(property: 'links', ref: '#/components/schemas/PaginationLinks'),
    new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
])]
#[OA\Schema(schema: 'ProductInput', required: ['name'], properties: [
    new OA\Property(property: 'brand_id', type: 'integer', nullable: true, example: 1),
    new OA\Property(property: 'subcategory_ids', type: 'array', nullable: true, items: new OA\Items(type: 'integer'), example: [1, 2]),
    new OA\Property(property: 'name', type: 'string', example: 'Cimento CP II 50kg'),
    new OA\Property(property: 'description', type: 'string', nullable: true),
])]
#[OA\Schema(schema: 'ProductImage', properties: [
    new OA\Property(property: 'id', type: 'integer', example: 1),
    new OA\Property(property: 'url', type: 'string', example: 'http://localhost/storage/products/cimento.png'),
    new OA\Property(property: 'alt_text', type: 'string', nullable: true),
    new OA\Property(property: 'position', type: 'integer', example: 0),
    new OA\Property(property: 'is_primary', type: 'boolean', example: true),
])]
#[OA\Schema(schema: 'Offer', properties: [
    new OA\Property(property: 'shop', type: 'object'),
    new OA\Property(property: 'regular_price', type: 'string', example: '50.00'),
    new OA\Property(property: 'effective_price', type: 'string', example: '40.00'),
    new OA\Property(property: 'is_promotion', type: 'boolean', example: true),
    new OA\Property(property: 'promotion_id', type: 'integer', nullable: true, example: 1),
    new OA\Property(property: 'available_quantity', type: 'string', example: '5.000'),
    new OA\Property(property: 'total_shop_stock', type: 'string', example: '10.000'),
    new OA\Property(property: 'promotion_ends_at', type: 'string', nullable: true, example: '2026-07-30T12:00:00.000000Z'),
])]
#[OA\Schema(schema: 'ProductSkuInput', required: ['product_id', 'sku', 'unit', 'transfer_batch_quantity', 'transfer_fee_per_batch'], properties: [
    new OA\Property(property: 'product_id', type: 'integer', example: 1),
    new OA\Property(property: 'sku', type: 'string', example: 'CIMENTO-50'),
    new OA\Property(property: 'barcode', type: 'string', nullable: true, example: '789000000001'),
    new OA\Property(property: 'unit', type: 'string', example: 'saco'),
    new OA\Property(property: 'weight', type: 'number', nullable: true, example: 50),
    new OA\Property(property: 'length', type: 'number', nullable: true, example: 60),
    new OA\Property(property: 'width', type: 'number', nullable: true, example: 40),
    new OA\Property(property: 'height', type: 'number', nullable: true, example: 10),
    new OA\Property(property: 'transfer_batch_quantity', type: 'number', example: 5),
    new OA\Property(property: 'transfer_fee_per_batch', type: 'number', example: 3),
])]
#[OA\Schema(schema: 'ProductSku', properties: [
    new OA\Property(property: 'id', type: 'integer', example: 1),
    new OA\Property(property: 'sku', type: 'string', example: 'CIMENTO-50'),
    new OA\Property(property: 'barcode', type: 'string', nullable: true),
    new OA\Property(property: 'unit', type: 'string', example: 'saco'),
    new OA\Property(property: 'weight', type: 'string', nullable: true, example: '50.000'),
    new OA\Property(property: 'dimensions', type: 'object'),
    new OA\Property(property: 'transfer', type: 'object'),
    new OA\Property(property: 'total_available', type: 'string', nullable: true, example: '10.000'),
    new OA\Property(property: 'best_offer', ref: '#/components/schemas/Offer', nullable: true),
    new OA\Property(property: 'offers', type: 'array', items: new OA\Items(ref: '#/components/schemas/Offer')),
])]
#[OA\Schema(schema: 'ProductSkuResponse', properties: [new OA\Property(property: 'data', ref: '#/components/schemas/ProductSku')])]
#[OA\Schema(schema: 'ProductSkusPaginatedResponse', properties: [
    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ProductSku')),
    new OA\Property(property: 'links', ref: '#/components/schemas/PaginationLinks'),
    new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
])]
#[OA\Schema(schema: 'Product', properties: [
    new OA\Property(property: 'id', type: 'integer', example: 1),
    new OA\Property(property: 'name', type: 'string', example: 'Cimento CP II 50kg'),
    new OA\Property(property: 'slug', type: 'string', example: 'cimento-cp-ii-50kg'),
    new OA\Property(property: 'description', type: 'string', nullable: true),
    new OA\Property(property: 'brand', ref: '#/components/schemas/Brand', nullable: true),
    new OA\Property(property: 'subcategories', type: 'array', items: new OA\Items(ref: '#/components/schemas/Subcategory')),
    new OA\Property(property: 'images', type: 'array', items: new OA\Items(ref: '#/components/schemas/ProductImage')),
    new OA\Property(property: 'skus', type: 'array', items: new OA\Items(ref: '#/components/schemas/ProductSku')),
])]
#[OA\Schema(schema: 'ProductResponse', properties: [new OA\Property(property: 'data', ref: '#/components/schemas/Product')])]
#[OA\Schema(schema: 'ProductsPaginatedResponse', properties: [
    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Product')),
    new OA\Property(property: 'links', ref: '#/components/schemas/PaginationLinks'),
    new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
])]
class CatalogSchema {}
