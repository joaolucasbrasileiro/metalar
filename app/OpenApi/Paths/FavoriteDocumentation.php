<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

class FavoriteDocumentation
{
    #[OA\Get(path: '/api/me/favorites', operationId: 'favoritesIndex', summary: 'Lista favoritos do usuario autenticado', tags: ['Favorites'], security: [['bearerAuth' => []]])]
    #[OA\Response(response: 200, description: 'Produtos favoritos paginados.', content: new OA\JsonContent(ref: '#/components/schemas/ProductsPaginatedResponse'))]
    #[OA\Response(response: 401, description: 'Nao autenticado.', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedError'))]
    public function index(): void {}

    #[OA\Post(path: '/api/me/favorites/{product}', operationId: 'favoritesStore', summary: 'Adiciona produto aos favoritos', tags: ['Favorites'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'product', description: 'Slug do produto.', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'cimento-cp-ii-50kg')]
    #[OA\Response(response: 201, description: 'Produto adicionado aos favoritos.', content: new OA\JsonContent(ref: '#/components/schemas/FavoriteStatusResponse'))]
    #[OA\Response(response: 200, description: 'Produto ja estava nos favoritos.', content: new OA\JsonContent(ref: '#/components/schemas/FavoriteStatusResponse'))]
    #[OA\Response(response: 401, description: 'Nao autenticado.', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedError'))]
    #[OA\Response(response: 404, description: 'Produto nao encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError'))]
    public function store(): void {}

    #[OA\Delete(path: '/api/me/favorites/{product}', operationId: 'favoritesDestroy', summary: 'Remove produto dos favoritos', tags: ['Favorites'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'product', description: 'Slug do produto.', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'cimento-cp-ii-50kg')]
    #[OA\Response(response: 200, description: 'Produto removido dos favoritos.', content: new OA\JsonContent(ref: '#/components/schemas/FavoriteStatusResponse'))]
    #[OA\Response(response: 401, description: 'Nao autenticado.', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedError'))]
    #[OA\Response(response: 404, description: 'Produto nao encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError'))]
    public function destroy(): void {}
}
