<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

class StaffCommerceDocumentation
{
    #[OA\Get(path: '/api/staff/shops/{shop}/stocks', operationId: 'staffStocksIndex', summary: 'Lista estoque da loja', tags: ['Staff Commerce'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'shop', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'matriz')]
    #[OA\Response(response: 200, description: 'Estoques paginados.', content: new OA\JsonContent(ref: '#/components/schemas/StocksPaginatedResponse'))]
    #[OA\Response(response: 403, description: 'Acesso negado.', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenError'))]
    public function stocksIndex(): void {}

    #[OA\Get(path: '/api/staff/shops/{shop}/stock-movements', operationId: 'staffStockMovementsIndex', summary: 'Lista movimentacoes de estoque', tags: ['Staff Commerce'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'shop', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'matriz')]
    #[OA\Response(response: 200, description: 'Movimentacoes paginadas.', content: new OA\JsonContent(ref: '#/components/schemas/StockMovementsPaginatedResponse'))]
    #[OA\Response(response: 403, description: 'Acesso negado.', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenError'))]
    public function stockMovementsIndex(): void {}

    #[OA\Post(path: '/api/staff/shops/{shop}/product-skus/{productSku}/stock-adjustments', operationId: 'staffStockAdjustmentsStore', summary: 'Ajusta estoque de SKU na loja', tags: ['Staff Commerce'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'shop', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'matriz')]
    #[OA\Parameter(name: 'productSku', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'CIMENTO-50')]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/StockAdjustmentRequest'))]
    #[OA\Response(response: 200, description: 'Estoque ajustado.', content: new OA\JsonContent(ref: '#/components/schemas/StockResponse'))]
    #[OA\Response(response: 403, description: 'Acesso negado.', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenError'))]
    #[OA\Response(response: 422, description: 'Dados invalidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function stockAdjustmentsStore(): void {}

    #[OA\Get(path: '/api/staff/shops/{shop}/prices', operationId: 'staffPricesIndex', summary: 'Lista precos da loja', tags: ['Staff Commerce'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'shop', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'matriz')]
    #[OA\Response(response: 200, description: 'Precos paginados.', content: new OA\JsonContent(ref: '#/components/schemas/ShopSkuPricesPaginatedResponse'))]
    #[OA\Response(response: 403, description: 'Acesso negado.', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenError'))]
    public function pricesIndex(): void {}

    #[OA\Put(path: '/api/staff/shops/{shop}/product-skus/{productSku}/price', operationId: 'staffPricesUpdate', summary: 'Cria ou atualiza preco de SKU na loja', tags: ['Staff Commerce'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'shop', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'matriz')]
    #[OA\Parameter(name: 'productSku', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'CIMENTO-50')]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ShopSkuPriceInput'))]
    #[OA\Response(response: 200, description: 'Preco atualizado.', content: new OA\JsonContent(ref: '#/components/schemas/ShopSkuPriceResponse'))]
    #[OA\Response(response: 201, description: 'Preco criado.', content: new OA\JsonContent(ref: '#/components/schemas/ShopSkuPriceResponse'))]
    #[OA\Response(response: 422, description: 'Dados invalidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function pricesUpdate(): void {}

    #[OA\Delete(path: '/api/staff/shops/{shop}/product-skus/{productSku}/price', operationId: 'staffPricesDestroy', summary: 'Remove preco de SKU na loja', tags: ['Staff Commerce'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'shop', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'matriz')]
    #[OA\Parameter(name: 'productSku', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'CIMENTO-50')]
    #[OA\Response(response: 204, description: 'Preco removido.')]
    #[OA\Response(response: 409, description: 'Preco possui promocao ativa.', content: new OA\JsonContent(ref: '#/components/schemas/ConflictError'))]
    public function pricesDestroy(): void {}

    #[OA\Get(path: '/api/staff/shops/{shop}/promotions', operationId: 'staffPromotionsIndex', summary: 'Lista promocoes da loja', tags: ['Staff Commerce'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'shop', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'matriz')]
    #[OA\Response(response: 200, description: 'Promocoes paginadas.', content: new OA\JsonContent(ref: '#/components/schemas/ShopSkuPromotionsPaginatedResponse'))]
    #[OA\Response(response: 403, description: 'Acesso negado.', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenError'))]
    public function promotionsIndex(): void {}

    #[OA\Post(path: '/api/staff/shops/{shop}/product-skus/{productSku}/promotions', operationId: 'staffPromotionsStore', summary: 'Cria promocao para SKU na loja', tags: ['Staff Commerce'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'shop', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'matriz')]
    #[OA\Parameter(name: 'productSku', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'CIMENTO-50')]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ShopSkuPromotionInput'))]
    #[OA\Response(response: 201, description: 'Promocao criada.', content: new OA\JsonContent(ref: '#/components/schemas/ShopSkuPromotionResponse'))]
    #[OA\Response(response: 422, description: 'Dados invalidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function promotionsStore(): void {}

    #[OA\Delete(path: '/api/staff/shops/{shop}/promotions/{promotion}', operationId: 'staffPromotionsDestroy', summary: 'Cancela promocao', tags: ['Staff Commerce'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'shop', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'matriz')]
    #[OA\Parameter(name: 'promotion', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)]
    #[OA\Response(response: 200, description: 'Promocao cancelada.', content: new OA\JsonContent(ref: '#/components/schemas/ShopSkuPromotionResponse'))]
    #[OA\Response(response: 404, description: 'Nao encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError'))]
    public function promotionsDestroy(): void {}
}
