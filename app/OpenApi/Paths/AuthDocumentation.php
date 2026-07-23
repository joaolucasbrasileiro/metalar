<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

class AuthDocumentation
{
    #[OA\Post(
        path: '/api/auth/register',
        operationId: 'authRegister',
        summary: 'Registra usuario',
        tags: ['Auth']
    )]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/RegisterRequest'))]
    #[OA\Response(response: 201, description: 'Conta criada.', content: new OA\JsonContent(ref: '#/components/schemas/UserResponse'))]
    #[OA\Response(response: 422, description: 'Dados invalidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function register(): void {}

    #[OA\Post(
        path: '/api/auth/login',
        operationId: 'authLogin',
        summary: 'Realiza login',
        description: 'Autentica um usuario ativo usando email ou CPF e retorna um token JWT.',
        tags: ['Auth']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest')
    )]
    #[OA\Response(
        response: 200,
        description: 'Login realizado com sucesso.',
        content: new OA\JsonContent(ref: '#/components/schemas/LoginResponse')
    )]
    #[OA\Response(
        response: 401,
        description: 'Credenciais invalidas.',
        content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedError')
    )]
    #[OA\Response(
        response: 422,
        description: 'Dados invalidos.',
        content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')
    )]
    public function login(): void {}

    #[OA\Get(
        path: '/api/auth/me',
        operationId: 'authMe',
        summary: 'Consulta usuario autenticado',
        tags: ['Auth'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(response: 200, description: 'Usuario autenticado.', content: new OA\JsonContent(ref: '#/components/schemas/UserResponse'))]
    #[OA\Response(response: 401, description: 'Nao autenticado.', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedError'))]
    public function me(): void {}

    #[OA\Post(
        path: '/api/auth/refresh',
        operationId: 'authRefresh',
        summary: 'Renova token JWT',
        tags: ['Auth'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(response: 200, description: 'Token renovado.', content: new OA\JsonContent(ref: '#/components/schemas/LoginResponse'))]
    #[OA\Response(response: 401, description: 'Nao autenticado.', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedError'))]
    public function refresh(): void {}

    #[OA\Post(
        path: '/api/auth/logout',
        operationId: 'authLogout',
        summary: 'Encerra sessao JWT',
        tags: ['Auth'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(response: 200, description: 'Logout realizado.', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse'))]
    #[OA\Response(response: 401, description: 'Nao autenticado.', content: new OA\JsonContent(ref: '#/components/schemas/UnauthorizedError'))]
    public function logout(): void {}
}
