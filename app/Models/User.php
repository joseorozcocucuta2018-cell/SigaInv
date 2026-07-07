<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserEstado;
use App\Notifications\CuentaEliminadaNotification;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthentication;
use Filament\Auth\MultiFactor\App\Concerns\InteractsWithAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Auth\MultiFactor\Email\Concerns\InteractsWithEmailAuthentication;
use Filament\Auth\MultiFactor\Email\Contracts\HasEmailAuthentication;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery, HasAvatar, HasEmailAuthentication
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;
    use InteractsWithAppAuthentication;
    use InteractsWithAppAuthenticationRecovery;
    use InteractsWithEmailAuthentication;

    protected $fillable = [
        'name',
        'email',
        'password',
        'celular',
        'fecha_nacimiento',
        'cargo',
        'avatar',
        'avatar_url',
        'custom_fields',
        'locale',
        'theme_color',
        'password_changed_at',
        'app_authentication_secret',
        'app_authentication_recovery_codes',
        'has_email_authentication',
        'estado',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'estado' => UserEstado::class,
            'fecha_nacimiento' => 'date',
            'password_changed_at' => 'datetime',
            'custom_fields' => 'array',
            'has_email_authentication' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user): void {
            // No notificar al usuario protegido del sistema
            if ($user->email === 'joseforozco@gmail.com') {
                return;
            }

            try {
                if ($user->estado?->value === UserEstado::ACTIVO->value) {
                    // Usuario activo eliminado por admin → correo de cuenta eliminada
                    $user->notify(new CuentaEliminadaNotification);
                } else {
                    // Usuario pendiente rechazado → correo de solicitud rechazada
                    $user->notify(new AccesoRechazadoNotification);
                }
            } catch (\Exception) {
                // No bloquear el borrado si el correo falla
            }
        });
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->estado !== UserEstado::ACTIVO) {
            return false;
        }

        $panelId = $panel->getId();

        return match ($panelId) {
            'admin' => $this->hasAnyRole(['administrador', 'auxiliar', 'contador', 'vendedor']),
            'clientes' => false,
            default => false,
        };
    }

    public function getFilamentAvatarUrl(): ?string
    {
        $avatarColumn = config('filament-edit-profile.avatar_column', 'avatar');

        return $this->$avatarColumn ? Storage::disk('directo')->url($this->$avatarColumn) : null;
    }

    /**
     * Verificar si el usuario es protegido (no puede ser eliminado)
     */
    public function isProtected(): bool
    {
        return $this->email === 'joseforozco@gmail.com';
    }

    /**
     * Verificar si el usuario puede ser eliminado
     */
    public function canBeDeleted(): bool
    {
        return ! $this->isProtected();
    }

    /**
     * Verificar si el usuario tiene registros vinculados que impiden su eliminación
     */
    public function hasRelatedRecords(): bool
    {
        $tables = [
            'ventas',
            'compras',
            'cotizaciones',
            'remisiones',
            'pago_clientes',
            'pago_proveedores',
            'movimientos_cajas',
            'movimientos_bancos',
            'movimientos_inventario',
            'ajustes_inventario',
            'conteos_fisicos',
            'devoluciones',
            'devoluciones_compras',
            'notas',
            'traslados',
            'transformaciones',
            'movimientos_saldo_cliente',
            'historico_precios',
            'auditorias',
        ];

        foreach ($tables as $table) {
            $exists = DB::table($table)
                ->where('usuario_id', $this->id)
                ->exists();

            if ($exists) {
                return true;
            }
        }

        return false;
    }
}
