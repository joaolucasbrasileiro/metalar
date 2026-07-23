<?php

namespace App\OpenApi\Paths;

use OpenApi\Attributes as OA;

class UserDocumentation
{
    #[OA\Get(path: '/api/users', operationId: 'usersIndex', summary: 'Lista usuarios', tags: ['Users'], security: [['bearerAuth' => []]])]
    #[OA\Response(response: 200, description: 'Lista paginada.', content: new OA\JsonContent(ref: '#/components/schemas/UsersPaginatedResponse'))]
    #[OA\Response(response: 403, description: 'Acesso negado.', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenError'))]
    public function index(): void {}

    #[OA\Get(path: '/api/users/{user}', operationId: 'usersShow', summary: 'Consulta usuario', tags: ['Users'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Usuario.', content: new OA\JsonContent(ref: '#/components/schemas/UserResponse'))]
    #[OA\Response(response: 403, description: 'Acesso negado.', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenError'))]
    #[OA\Response(response: 404, description: 'Nao encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError'))]
    public function show(): void {}

    #[OA\Patch(path: '/api/users/{user}', operationId: 'usersUpdate', summary: 'Atualiza perfil', tags: ['Users'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateUserRequest'))]
    #[OA\Response(response: 200, description: 'Usuario atualizado.', content: new OA\JsonContent(ref: '#/components/schemas/UserResponse'))]
    #[OA\Response(response: 403, description: 'Acesso negado.', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenError'))]
    #[OA\Response(response: 422, description: 'Dados invalidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function update(): void {}

    #[OA\Patch(path: '/api/users/{user}/cpf', operationId: 'usersUpdateCpf', summary: 'Atualiza CPF', tags: ['Users'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateCpfRequest'))]
    #[OA\Response(response: 200, description: 'CPF atualizado.', content: new OA\JsonContent(ref: '#/components/schemas/UserResponse'))]
    #[OA\Response(response: 422, description: 'Dados invalidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function updateCpf(): void {}

    #[OA\Patch(path: '/api/users/{user}/password', operationId: 'usersUpdatePassword', summary: 'Atualiza senha', tags: ['Users'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdatePasswordRequest'))]
    #[OA\Response(response: 200, description: 'Senha atualizada.', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse'))]
    #[OA\Response(response: 422, description: 'Dados invalidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function updatePassword(): void {}

    #[OA\Patch(path: '/api/users/{user}/role', operationId: 'usersUpdateRole', summary: 'Atualiza papel do usuario', tags: ['Users'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateRoleRequest'))]
    #[OA\Response(response: 200, description: 'Papel atualizado.', content: new OA\JsonContent(ref: '#/components/schemas/UserResponse'))]
    #[OA\Response(response: 403, description: 'Acesso negado.', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenError'))]
    #[OA\Response(response: 422, description: 'Dados invalidos.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))]
    public function updateRole(): void {}

    #[OA\Delete(path: '/api/users/{user}', operationId: 'usersDestroy', summary: 'Remove usuario', tags: ['Users'], security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 204, description: 'Usuario removido.')]
    #[OA\Response(response: 403, description: 'Acesso negado.', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenError'))]
    #[OA\Response(response: 404, description: 'Nao encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError'))]
    public function destroy(): void {}
}
