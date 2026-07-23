<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MessageResponse',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Operacao realizada com sucesso.'),
    ]
)]
#[OA\Schema(
    schema: 'ValidationError',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(
                type: 'array',
                items: new OA\Items(type: 'string')
            )
        ),
    ]
)]
#[OA\Schema(
    schema: 'ForbiddenError',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Voce nao tem permissao para acessar este recurso.'),
    ]
)]
#[OA\Schema(
    schema: 'NotFoundError',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Resource not found.'),
    ]
)]
#[OA\Schema(
    schema: 'ConflictError',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Nao e possivel remover este recurso.'),
    ]
)]
#[OA\Schema(
    schema: 'PaginationLinks',
    properties: [
        new OA\Property(property: 'first', type: 'string', nullable: true, example: 'http://localhost/api/products?page=1'),
        new OA\Property(property: 'last', type: 'string', nullable: true, example: 'http://localhost/api/products?page=3'),
        new OA\Property(property: 'prev', type: 'string', nullable: true),
        new OA\Property(property: 'next', type: 'string', nullable: true),
    ]
)]
#[OA\Schema(
    schema: 'PaginationMeta',
    properties: [
        new OA\Property(property: 'current_page', type: 'integer', example: 1),
        new OA\Property(property: 'from', type: 'integer', nullable: true, example: 1),
        new OA\Property(property: 'last_page', type: 'integer', example: 3),
        new OA\Property(property: 'path', type: 'string', example: 'http://localhost/api/products'),
        new OA\Property(property: 'per_page', type: 'integer', example: 15),
        new OA\Property(property: 'to', type: 'integer', nullable: true, example: 15),
        new OA\Property(property: 'total', type: 'integer', example: 40),
    ]
)]
class CommonSchema {}
