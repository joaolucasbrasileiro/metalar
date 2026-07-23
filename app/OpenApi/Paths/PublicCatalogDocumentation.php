<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

class PublicCatalogDocumentation
{
    #[OA\Get(path: '/api/shops', operationId: 'shopsIndex', summary: 'Lista lojas', tags: ['Public Catalog'])]
    #[OA\Response(response: 200, description: 'Lojas.', content: new OA\JsonContent(ref: '#/components/schemas/ShopsResponse'))]
    public function shopsIndex(): void {}

    #[OA\Get(path: '/api/shops/{shop}', operationId: 'shopsShow', summary: 'Consulta loja', tags: ['Public Catalog'])]
    #[OA\Parameter(name: 'shop', description: 'Codigo da loja.', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'matriz')]
    #[OA\Response(response: 200, description: 'Loja.', content: new OA\JsonContent(ref: '#/components/schemas/ShopResponse'))]
    #[OA\Response(response: 404, description: 'Nao encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError'))]
    public function shopsShow(): void {}

    #[OA\Get(path: '/api/brands', operationId: 'brandsIndex', summary: 'Lista marcas', tags: ['Public Catalog'])]
    #[OA\Response(response: 200, description: 'Marcas paginadas.', content: new OA\JsonContent(ref: '#/components/schemas/BrandsPaginatedResponse'))]
    public function brandsIndex(): void {}

    #[OA\Get(path: '/api/brands/{brand}', operationId: 'brandsShow', summary: 'Consulta marca', tags: ['Public Catalog'])]
    #[OA\Parameter(name: 'brand', description: 'Slug da marca.', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'votorantim')]
    #[OA\Response(response: 200, description: 'Marca.', content: new OA\JsonContent(ref: '#/components/schemas/BrandResponse'))]
    #[OA\Response(response: 404, description: 'Nao encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError'))]
    public function brandsShow(): void {}

    #[OA\Get(path: '/api/products', operationId: 'productsIndex', summary: 'Lista produtos', tags: ['Public Catalog'])]
    #[OA\Parameter(name: 'category', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'cimentos')]
    #[OA\Parameter(name: 'subcategory', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'cimento-cp-ii')]
    #[OA\Parameter(name: 'brand', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'votorantim')]
    #[OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'cimento')]
    #[OA\Parameter(name: 'in_stock', in: 'query', required: false, schema: new OA\Schema(type: 'boolean'), example: true)]
    #[OA\Parameter(name: 'sort', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['best_offer', 'name']), example: 'best_offer')]
    #[OA\Response(response: 200, description: 'Produtos paginados.', content: new OA\JsonContent(ref: '#/components/schemas/ProductsPaginatedResponse'))]
    #[OA\Response(response: 422, description: 'Filtros invalidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function productsIndex(): void {}

    #[OA\Get(path: '/api/products/{product}', operationId: 'productsShow', summary: 'Consulta produto', tags: ['Public Catalog'])]
    #[OA\Parameter(name: 'product', description: 'Slug do produto.', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'cimento-cp-ii-50kg')]
    #[OA\Response(response: 200, description: 'Produto.', content: new OA\JsonContent(ref: '#/components/schemas/ProductResponse'))]
    #[OA\Response(response: 404, description: 'Nao encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError'))]
    public function productsShow(): void {}

    #[OA\Get(path: '/api/categories', operationId: 'categoriesIndex', summary: 'Lista categorias', tags: ['Public Catalog'])]
    #[OA\Response(response: 200, description: 'Categorias paginadas.', content: new OA\JsonContent(ref: '#/components/schemas/CategoriesPaginatedResponse'))]
    public function categoriesIndex(): void {}

    #[OA\Get(path: '/api/categories/{category}', operationId: 'categoriesShow', summary: 'Consulta categoria', tags: ['Public Catalog'])]
    #[OA\Parameter(name: 'category', description: 'Slug da categoria.', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'cimentos')]
    #[OA\Response(response: 200, description: 'Categoria.', content: new OA\JsonContent(ref: '#/components/schemas/CategoryResponse'))]
    #[OA\Response(response: 404, description: 'Nao encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError'))]
    public function categoriesShow(): void {}

    #[OA\Get(path: '/api/subcategories', operationId: 'subcategoriesIndex', summary: 'Lista subcategorias raiz', tags: ['Public Catalog'])]
    #[OA\Response(response: 200, description: 'Subcategorias paginadas.', content: new OA\JsonContent(ref: '#/components/schemas/SubcategoriesPaginatedResponse'))]
    public function subcategoriesIndex(): void {}

    #[OA\Get(path: '/api/subcategories/{subcategory}', operationId: 'subcategoriesShow', summary: 'Consulta subcategoria', tags: ['Public Catalog'])]
    #[OA\Parameter(name: 'subcategory', description: 'Slug da subcategoria.', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'cimento-cp-ii')]
    #[OA\Response(response: 200, description: 'Subcategoria.', content: new OA\JsonContent(ref: '#/components/schemas/SubcategoryResponse'))]
    #[OA\Response(response: 404, description: 'Nao encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError'))]
    public function subcategoriesShow(): void {}

    #[OA\Get(path: '/api/product-skus', operationId: 'productSkusIndex', summary: 'Lista SKUs', tags: ['Public Catalog'])]
    #[OA\Response(response: 200, description: 'SKUs paginados.', content: new OA\JsonContent(ref: '#/components/schemas/ProductSkusPaginatedResponse'))]
    public function productSkusIndex(): void {}

    #[OA\Get(path: '/api/product-skus/{productSku}', operationId: 'productSkusShow', summary: 'Consulta SKU', tags: ['Public Catalog'])]
    #[OA\Parameter(name: 'productSku', description: 'Codigo SKU.', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'CIMENTO-50')]
    #[OA\Response(response: 200, description: 'SKU.', content: new OA\JsonContent(ref: '#/components/schemas/ProductSkuResponse'))]
    #[OA\Response(response: 404, description: 'Nao encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError'))]
    public function productSkusShow(): void {}
}
