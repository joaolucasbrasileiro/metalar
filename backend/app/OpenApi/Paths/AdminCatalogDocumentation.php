<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

class AdminCatalogDocumentation
{
    #[OA\Post(path: '/api/admin/brands', operationId: 'adminBrandsStore', summary: 'Cria marca', tags: ['Admin Catalog'], security: [['bearerAuth' => []]])]
    #[OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(required: ['name', 'logo'], properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Votorantim'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'logo', type: 'string', format: 'binary'),
    ])))]
    #[OA\Response(response: 201, description: 'Marca criada.', content: new OA\JsonContent(ref: '#/components/schemas/BrandResponse'))]
    #[OA\Response(response: 401, description: 'Nao autenticado.', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedError'))]
    #[OA\Response(response: 403, description: 'Acesso negado.', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenError'))]
    #[OA\Response(response: 422, description: 'Dados invalidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function brandsStore(): void {}

    #[OA\Patch(path: '/api/admin/brands/{brand}', operationId: 'adminBrandsUpdate', summary: 'Atualiza marca', tags: ['Admin Catalog'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'brand', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'votorantim')]
    #[OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Votorantim'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'logo', type: 'string', format: 'binary'),
    ])))]
    #[OA\Response(response: 200, description: 'Marca atualizada.', content: new OA\JsonContent(ref: '#/components/schemas/BrandResponse'))]
    #[OA\Response(response: 422, description: 'Dados invalidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function brandsUpdate(): void {}

    #[OA\Delete(path: '/api/admin/brands/{brand}', operationId: 'adminBrandsDestroy', summary: 'Remove marca', tags: ['Admin Catalog'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'brand', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'votorantim')]
    #[OA\Response(response: 204, description: 'Marca removida.')]
    #[OA\Response(response: 409, description: 'Marca possui produtos.', content: new OA\JsonContent(ref: '#/components/schemas/ConflictError'))]
    public function brandsDestroy(): void {}

    #[OA\Post(path: '/api/admin/products', operationId: 'adminProductsStore', summary: 'Cria produto', tags: ['Admin Catalog'], security: [['bearerAuth' => []]])]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ProductInput'))]
    #[OA\Response(response: 201, description: 'Produto criado.', content: new OA\JsonContent(ref: '#/components/schemas/ProductResponse'))]
    #[OA\Response(response: 422, description: 'Dados invalidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function productsStore(): void {}

    #[OA\Patch(path: '/api/admin/products/{product}', operationId: 'adminProductsUpdate', summary: 'Atualiza produto', tags: ['Admin Catalog'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'cimento-cp-ii-50kg')]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ProductInput'))]
    #[OA\Response(response: 200, description: 'Produto atualizado.', content: new OA\JsonContent(ref: '#/components/schemas/ProductResponse'))]
    #[OA\Response(response: 422, description: 'Dados invalidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function productsUpdate(): void {}

    #[OA\Delete(path: '/api/admin/products/{product}', operationId: 'adminProductsDestroy', summary: 'Remove produto', tags: ['Admin Catalog'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'cimento-cp-ii-50kg')]
    #[OA\Response(response: 204, description: 'Produto removido.')]
    #[OA\Response(response: 409, description: 'Produto possui SKUs.', content: new OA\JsonContent(ref: '#/components/schemas/ConflictError'))]
    public function productsDestroy(): void {}

    #[OA\Post(path: '/api/admin/categories', operationId: 'adminCategoriesStore', summary: 'Cria categoria', tags: ['Admin Catalog'], security: [['bearerAuth' => []]])]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/CategoryInput'))]
    #[OA\Response(response: 201, description: 'Categoria criada.', content: new OA\JsonContent(ref: '#/components/schemas/CategoryResponse'))]
    #[OA\Response(response: 422, description: 'Dados invalidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function categoriesStore(): void {}

    #[OA\Patch(path: '/api/admin/categories/{category}', operationId: 'adminCategoriesUpdate', summary: 'Atualiza categoria', tags: ['Admin Catalog'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'category', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'cimentos')]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/CategoryInput'))]
    #[OA\Response(response: 200, description: 'Categoria atualizada.', content: new OA\JsonContent(ref: '#/components/schemas/CategoryResponse'))]
    #[OA\Response(response: 422, description: 'Dados invalidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function categoriesUpdate(): void {}

    #[OA\Delete(path: '/api/admin/categories/{category}', operationId: 'adminCategoriesDestroy', summary: 'Remove categoria', tags: ['Admin Catalog'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'category', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'cimentos')]
    #[OA\Response(response: 204, description: 'Categoria removida.')]
    #[OA\Response(response: 409, description: 'Categoria possui subcategorias.', content: new OA\JsonContent(ref: '#/components/schemas/ConflictError'))]
    public function categoriesDestroy(): void {}

    #[OA\Post(path: '/api/admin/subcategories', operationId: 'adminSubcategoriesStore', summary: 'Cria subcategoria', tags: ['Admin Catalog'], security: [['bearerAuth' => []]])]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/SubcategoryInput'))]
    #[OA\Response(response: 201, description: 'Subcategoria criada.', content: new OA\JsonContent(ref: '#/components/schemas/SubcategoryResponse'))]
    #[OA\Response(response: 422, description: 'Dados invalidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function subcategoriesStore(): void {}

    #[OA\Patch(path: '/api/admin/subcategories/{subcategory}', operationId: 'adminSubcategoriesUpdate', summary: 'Atualiza subcategoria', tags: ['Admin Catalog'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'subcategory', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'cimento-cp-ii')]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/SubcategoryInput'))]
    #[OA\Response(response: 200, description: 'Subcategoria atualizada.', content: new OA\JsonContent(ref: '#/components/schemas/SubcategoryResponse'))]
    #[OA\Response(response: 422, description: 'Dados invalidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function subcategoriesUpdate(): void {}

    #[OA\Delete(path: '/api/admin/subcategories/{subcategory}', operationId: 'adminSubcategoriesDestroy', summary: 'Remove subcategoria', tags: ['Admin Catalog'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'subcategory', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'cimento-cp-ii')]
    #[OA\Response(response: 204, description: 'Subcategoria removida.')]
    #[OA\Response(response: 409, description: 'Subcategoria possui filhas ou produtos.', content: new OA\JsonContent(ref: '#/components/schemas/ConflictError'))]
    public function subcategoriesDestroy(): void {}

    #[OA\Post(path: '/api/admin/product-skus', operationId: 'adminProductSkusStore', summary: 'Cria SKU', tags: ['Admin Catalog'], security: [['bearerAuth' => []]])]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ProductSkuInput'))]
    #[OA\Response(response: 201, description: 'SKU criado.', content: new OA\JsonContent(ref: '#/components/schemas/ProductSkuResponse'))]
    #[OA\Response(response: 422, description: 'Dados invalidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function productSkusStore(): void {}

    #[OA\Patch(path: '/api/admin/product-skus/{productSku}', operationId: 'adminProductSkusUpdate', summary: 'Atualiza SKU', tags: ['Admin Catalog'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'productSku', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'CIMENTO-50')]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ProductSkuInput'))]
    #[OA\Response(response: 200, description: 'SKU atualizado.', content: new OA\JsonContent(ref: '#/components/schemas/ProductSkuResponse'))]
    #[OA\Response(response: 422, description: 'Dados invalidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function productSkusUpdate(): void {}

    #[OA\Delete(path: '/api/admin/product-skus/{productSku}', operationId: 'adminProductSkusDestroy', summary: 'Remove SKU', tags: ['Admin Catalog'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'productSku', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'CIMENTO-50')]
    #[OA\Response(response: 204, description: 'SKU removido.')]
    #[OA\Response(response: 409, description: 'SKU possui estoque ou precos.', content: new OA\JsonContent(ref: '#/components/schemas/ConflictError'))]
    public function productSkusDestroy(): void {}

    #[OA\Post(path: '/api/admin/product-images', operationId: 'adminProductImagesStore', summary: 'Envia imagem de produto', tags: ['Admin Catalog'], security: [['bearerAuth' => []]])]
    #[OA\RequestBody(required: true, content: new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(required: ['product_id', 'image'], properties: [
        new OA\Property(property: 'product_id', type: 'integer', example: 1),
        new OA\Property(property: 'image', type: 'string', format: 'binary'),
        new OA\Property(property: 'alt_text', type: 'string', nullable: true),
        new OA\Property(property: 'position', type: 'integer', example: 0),
        new OA\Property(property: 'is_primary', type: 'boolean', example: true),
    ])))]
    #[OA\Response(response: 201, description: 'Imagem criada.', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/ProductImage')]))]
    #[OA\Response(response: 422, description: 'Dados invalidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function productImagesStore(): void {}

    #[OA\Delete(path: '/api/admin/product-images/{productImage}', operationId: 'adminProductImagesDestroy', summary: 'Remove imagem de produto', tags: ['Admin Catalog'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'productImage', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)]
    #[OA\Response(response: 204, description: 'Imagem removida.')]
    #[OA\Response(response: 404, description: 'Nao encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError'))]
    public function productImagesDestroy(): void {}

    #[OA\Post(path: '/api/admin/shops/{shop}/users/{user}', operationId: 'adminShopUsersStore', summary: 'Vincula usuario a loja', tags: ['Admin Catalog'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'shop', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'matriz')]
    #[OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)]
    #[OA\Response(response: 204, description: 'Usuario vinculado.')]
    public function shopUsersStore(): void {}

    #[OA\Delete(path: '/api/admin/shops/{shop}/users/{user}', operationId: 'adminShopUsersDestroy', summary: 'Remove vinculo do usuario com loja', tags: ['Admin Catalog'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'shop', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'matriz')]
    #[OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)]
    #[OA\Response(response: 204, description: 'Vinculo removido.')]
    public function shopUsersDestroy(): void {}
}
