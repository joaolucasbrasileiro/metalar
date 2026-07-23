<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'RegisterRequest',
    required: ['name', 'email', 'password', 'cpf', 'phone'],
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Lucas Silva'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'lucas@email.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'Senha@123'),
        new OA\Property(property: 'birthday', type: 'string', format: 'date', nullable: true, example: '1992-03-15'),
        new OA\Property(property: 'cpf', type: 'string', example: '12345678909'),
        new OA\Property(property: 'phone', type: 'string', example: '71999998888'),
    ]
)]
#[OA\Schema(
    schema: 'User',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Lucas Silva'),
        new OA\Property(property: 'email', type: 'string', example: 'lucas@email.com'),
        new OA\Property(property: 'birthday', type: 'string', nullable: true, example: '15/03/1992'),
        new OA\Property(property: 'cpf', type: 'string', nullable: true, example: '***.***.***-09'),
        new OA\Property(property: 'phone', type: 'string', nullable: true, example: '(71) 99999-8888'),
        new OA\Property(property: 'role', type: 'string', enum: ['common', 'moderator', 'admin'], example: 'common'),
        new OA\Property(property: 'created_at', type: 'string', nullable: true, example: '23/07/2026 10:00:00'),
        new OA\Property(property: 'updated_at', type: 'string', nullable: true, example: '23/07/2026 10:00:00'),
    ]
)]
#[OA\Schema(
    schema: 'UserResponse',
    properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/User'),
    ]
)]
#[OA\Schema(
    schema: 'UsersPaginatedResponse',
    properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/User')),
        new OA\Property(property: 'links', ref: '#/components/schemas/PaginationLinks'),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ]
)]
#[OA\Schema(
    schema: 'UpdateUserRequest',
    properties: [
        new OA\Property(property: 'name', type: 'string', example: 'Lucas Silva'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'lucas@email.com'),
        new OA\Property(property: 'birthday', type: 'string', format: 'date', nullable: true, example: '1992-03-15'),
        new OA\Property(property: 'phone', type: 'string', example: '71999998888'),
    ]
)]
#[OA\Schema(
    schema: 'UpdateCpfRequest',
    required: ['cpf'],
    properties: [
        new OA\Property(property: 'cpf', type: 'string', example: '12345678909'),
    ]
)]
#[OA\Schema(
    schema: 'UpdatePasswordRequest',
    required: ['current_password', 'password', 'password_confirmation'],
    properties: [
        new OA\Property(property: 'current_password', type: 'string', format: 'password', example: 'Senha@123'),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'NovaSenha@123'),
        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'NovaSenha@123'),
    ]
)]
#[OA\Schema(
    schema: 'UpdateRoleRequest',
    required: ['role'],
    properties: [
        new OA\Property(property: 'role', type: 'string', enum: ['common', 'moderator', 'admin'], example: 'moderator'),
    ]
)]
class UserSchema {}
