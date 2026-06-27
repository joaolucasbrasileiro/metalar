<?php

namespace App\Http\Resources;

use App\Enums\UserRole;
use App\Support\Formatters\CpfFormatter;
use App\Support\Formatters\PhoneFormatter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,
            'email' => $this->email,

            'birthday' => $this->birthday?->format('d/m/Y'),

            'cpf' => CpfFormatter::mask($this->cpf),

            'phone' => PhoneFormatter::format($this->phone),

            'role' => $this->role?->value,

            'created_at' => $this->created_at?->format('d/m/Y H:i:s'),
            'updated_at' => $this->updated_at?->format('d/m/Y H:i:s'),

            '_links' => $this->links($request),
        ];
    }

    private function links(Request $request): array
    {
        $links = [
            'self' => [
                'href' => url("/api/users/{$this->id}"),
                'method' => 'GET',
            ],
        ];

        $authUser = Auth::guard('api')->user();

        if ($authUser && $authUser->id === $this->id) {
            $links['update'] = [
                'href' => url("/api/users/{$this->id}"),
                'method' => 'PATCH',
            ];

            $links['update_cpf'] = [
                'href' => url("/api/users/{$this->id}/cpf"),
                'method' => 'PATCH',
            ];

            $links['update_password'] = [
                'href' => url("/api/users/{$this->id}/password"),
                'method' => 'PATCH',
            ];

            $links['delete'] = [
                'href' => url("/api/users/{$this->id}"),
                'method' => 'DELETE',
            ];
        }

        if ($authUser && $authUser->role === UserRole::ADMIN) {
            $links['update_role'] = [
                'href' => url("/api/users/{$this->id}/role"),
                'method' => 'PATCH',
            ];
        }

        return $links;
    }

}
