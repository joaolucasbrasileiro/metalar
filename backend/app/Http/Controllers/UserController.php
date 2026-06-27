<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\UpdateUserCpfRequest;
use App\Http\Requests\UpdateUserPasswordRequest;
use App\Http\Requests\UpdateUserRoleRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    //Busca todos os usuários cadastrados na db
    public function index(): AnonymousResourceCollection
    {
        $this->authorizeAdminAccess();

        $users = User::paginate(10);

        return UserResource::collection($users);
    }

    //Exibe um usuário específico por ID (query params)
    public function show(User $user): UserResource
    {
        $this->authorizeUserAccess($user);

        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $this->authorizeUserAccess($user);

        $user->update($request -> validated());

        return new UserResource($user);
    }

    public function updateCpf(UpdateUserCpfRequest $request, User $user): UserResource
    {
        $this->authorizeUserAccess($user);

        $user->cpf = $request -> validated()['cpf'];
        $user->save();

        return new UserResource($user);
    }

    public function updatePassword(UpdateUserPasswordRequest $request, User $user): JsonResponse
    {
        $this->authorizeUserAccess($user);

        $user->password = $request -> validated()['password'];
        $user->save();

        return response()->json([
            'message' => 'Sua senha atualizada com sucesso!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorizeUserAccess($user);

        $user->delete();

        return response()->json(null, 204);
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user): UserResource
    {
        $this->authorizeAdminAccess();

        $user->role = $request -> validated()['role'];
        $user->save();

        return new UserResource($user);
    }

    private function authorizeUserAccess(User $user): void
    {
        abort_if(Auth::guard('api')->id() !== $user->id, 403, 'Voce nao tem permissao para acessar este usuario.');
    }

    private function authorizeAdminAccess(): void
    {
        abort_if(Auth::guard('api')->user()->role !== UserRole::ADMIN, 403, 'Voce nao tem permissao para acessar este recurso.');
    }
}
