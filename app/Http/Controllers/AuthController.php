<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResendActivationRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(StoreUserRequest $request): JsonResponse
    {
        $user = new User($request->validated());
        $user->forceFill([
            'email_verified_at' => null,
            'is_active' => false,
        ]);
        $user->save();

        $user->refresh();
        $user->sendActivationNotification();

        return (new UserResource($user))
            ->additional([
                'message' => 'Conta criada com sucesso. Enviamos um email para ativacao da conta.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $login = $validated['login'];

        $field = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'cpf';

        $credentials = [
            $field => $login,
            'password' => $validated['password'],
            'is_active' => true,
        ];

        $inactiveUser = User::where($field, $login)->first();

        if ($inactiveUser && ! $inactiveUser->is_active && Hash::check($validated['password'], $inactiveUser->password)) {
            return response()->json([
                'message' => 'Ative sua conta pelo email enviado antes de entrar.',
            ], 403);
        }

        $token = Auth::guard('api')->attempt($credentials);

        if (! $token) {
            return response()->json([
                'message' => 'Credenciais invalidas.',
            ], 401);
        }

        return $this->respondWithToken($token);

    }

    public function activate(Request $request, User $user, string $hash): JsonResponse|RedirectResponse
    {
        if (! $request->hasValidSignature() || ! hash_equals(sha1($user->email), $hash)) {
            return $this->activationResponse(
                $request,
                'Link de ativacao invalido ou expirado.',
                false,
                403,
            );
        }

        if ($user->is_active) {
            return $this->activationResponse($request, 'Conta ja ativada.', true);
        }

        $user->forceFill([
            'email_verified_at' => $user->email_verified_at ?? now(),
            'is_active' => true,
        ])->save();

        return $this->activationResponse($request, 'Conta ativada com sucesso.', true);
    }

    public function resendActivation(ResendActivationRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if ($user && ! $user->is_active) {
            $user->sendActivationNotification();
        }

        return response()->json([
            'message' => 'Se a conta existir e precisar de ativacao, enviaremos um novo email.',
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_THROTTLED) {
            return response()->json([
                'message' => __($status),
            ], 429);
        }

        return response()->json([
            'message' => 'Se o email existir, enviaremos instrucoes para redefinir a senha.',
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json([
                'message' => __($status),
            ], 422);
        }

        return response()->json([
            'message' => 'Senha redefinida com sucesso.',
        ]);
    }

    public function me(): UserResource
    {
        return new UserResource(Auth::guard('api')->user());
    }

    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(Auth::guard('api')->refresh());
    }

    public function logout(): JsonResponse
    {
        Auth::guard('api')->logout();

        return response()->json([
            'message' => 'Logout realizado com sucesso.',
        ]);
    }

    private function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            'user' => new UserResource(Auth::guard('api')->user()),
        ]);
    }

    private function activationResponse(
        Request $request,
        string $message,
        bool $activated,
        int $status = 200,
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], $status);
        }

        return redirect()->away($this->frontendUrl('/login', [
            'activation' => $activated ? 'success' : 'failed',
        ]));
    }

    private function frontendUrl(string $path, array $query = []): string
    {
        $url = rtrim((string) config('services.frontend.url'), '/').'/'.ltrim($path, '/');

        return $query === [] ? $url : $url.'?'.http_build_query($query);
    }
}
