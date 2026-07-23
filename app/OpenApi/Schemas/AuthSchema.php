<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LoginRequest',
    required: ['login', 'password'],
    properties: [
        new OA\Property(
            property: 'login',
            description: 'Email ou CPF do usuario.',
            type: 'string',
            example: 'usuario@email.com'
        ),
        new OA\Property(
            property: 'password',
            type: 'string',
            format: 'password',
            example: 'Senha@123'
        ),
    ]
)]
#[OA\Schema(
    schema: 'AuthUser',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Usuario Exemplo'),
        new OA\Property(property: 'email', type: 'string', example: 'usuario@email.com'),
        new OA\Property(property: 'role', type: 'string', example: 'common'),
    ]
)]
#[OA\Schema(
    schema: 'LoginResponse',
    properties: [
        new OA\Property(
            property: 'access_token',
            type: 'string',
            example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'
        ),
        new OA\Property(
            property: 'token_type',
            type: 'string',
            example: 'bearer'
        ),
        new OA\Property(
            property: 'expires_in',
            type: 'integer',
            example: 3600
        ),
        new OA\Property(
            property: 'user',
            ref: '#/components/schemas/AuthUser'
        ),
    ]
)]
#[OA\Schema(
    schema: 'UnauthorizedError',
    properties: [
        new OA\Property(
            property: 'message',
            type: 'string',
            example: 'Credenciais invalidas.'
        ),
    ]
)]
class AuthSchema {}
