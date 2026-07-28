<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\PersonType;
use App\Enums\UserRole;
use App\Notifications\ActivateAccountNotification;
use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\URL;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

#[Fillable(['name', 'email', 'password', 'birthday', 'cpf', 'phone', 'cnpj', 'person_type', 'rules'])]
#[Hidden(['password', 'remember_token', 'cpf', 'phone', 'cnpj'])]
class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
            'role' => UserRole::class,
            'person_type' => PersonType::class,
            'rules' => 'boolean',
            'birthday' => 'date',
        ];
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function sendActivationNotification(): void
    {
        $this->notify(new ActivateAccountNotification($this->activationUrl()));
    }

    public function sendPasswordResetNotification($token): void
    {
        $resetUrl = URL::query($this->frontendUrl('/reset-password'), [
            'token' => $token,
            'email' => $this->email,
        ]);

        $this->notify(new ResetPasswordNotification($resetUrl));
    }

    private function activationUrl(): string
    {
        return URL::temporarySignedRoute(
            'auth.activate',
            now()->addMinutes(60),
            [
                'user' => $this->id,
                'hash' => sha1($this->email),
            ],
        );
    }

    private function frontendUrl(string $path): string
    {
        return rtrim((string) config('services.frontend.url'), '/').'/'.ltrim($path, '/');
    }

    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class)->withTimestamps();
    }

    public function favoriteProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'favorite_products')->withTimestamps();
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function createdPromotions(): HasMany
    {
        return $this->hasMany(ShopSkuPromotion::class, 'created_by_user_id');
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
