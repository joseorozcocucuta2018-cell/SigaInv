<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ClienteEstado;
use App\Enums\DocumentoTipoEnum;
use App\Enums\PortalAccesoEnum;
use App\Models\Traits\SanitizesAttributes;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Cliente extends Authenticatable implements AuthenticatableContract, FilamentUser, HasName
{
    use HasFactory;
    use Notifiable;
    use SanitizesAttributes;

    protected $table = 'clientes';

    protected $fillable = [
        'nombre',
        'documento',
        'tipo_documento',
        'telefono',
        'email',
        'direccion1',
        'direccion2',
        'departamento_id',
        'ciudad_id',
        'saldo',
        'pais',
        'estado',
        'portal_acceso',
        'limite_credito',
        'dias_credito',
        'dias_pago',
        'contacto_principal',
        'sitio_web',
        'porcentaje_descuento',
        'usuario_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'pais' => 'Colombia',
    ];

    protected function casts(): array
    {
        return [
            'estado' => ClienteEstado::class,
            'tipo_documento' => DocumentoTipoEnum::class,
            'portal_acceso' => PortalAccesoEnum::class,
            'saldo' => 'decimal:2',
            'limite_credito' => 'decimal:2',
            'porcentaje_descuento' => 'decimal:2',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'password_changed_at' => 'datetime',
            'portal_last_login_at' => 'datetime',
        ];
    }

    public function ciudad(): BelongsTo
    {
        return $this->belongsTo(Ciudad::class, 'ciudad_id');
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Identificador único de auth — siempre 'id'.
     */
    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    /**
     * Valor del identificador (PK) usado por el guard 'cliente'.
     */
    public function getAuthIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Nombre de la columna que almacena el password hasheado.
     */
    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    /**
     * Valor del password (ya hasheado por el cast 'hashed').
     */
    public function getAuthPassword(): string
    {
        return (string) ($this->password ?? '');
    }

    public function getRememberToken(): string
    {
        if (! empty($this->remember_token)) {
            return (string) $this->remember_token;
        }

        return '';
    }

    public function setRememberToken($value): void
    {
        $this->remember_token = $value;
    }

    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }

    /**
     * Verifica si el cliente tiene credenciales activas (password seteado).
     * Es la condición que el Login del portal valida antes de permitir
     * acceso — sin password, el cliente NO existe para el guard 'cliente'.
     */
    public function tieneAccesoPortal(): bool
    {
        return ! empty($this->password)
            && $this->estado === ClienteEstado::ACTIVO;
    }

    /**
     * Verifica si el cliente debe cambiar su contraseña (temporal).
     * Null en password_changed_at = contraseña temporal asignada por admin.
     */
    public function debeCambiarPassword(): bool
    {
        return $this->password_changed_at === null;
    }

    /**
     * Calcula el precio con descuento para un producto.
     * Retorna precio_venta si no hay descuento.
     */
    public function aplicarDescuento(float $precioVenta): float
    {
        if ($this->porcentaje_descuento > 0) {
            return round($precioVenta * (1 - $this->porcentaje_descuento / 100), 2);
        }

        return $precioVenta;
    }

    /**
     * Determina si el cliente puede acceder al panel.
     *
     * Invocado por Filament\Http\Middleware\Authenticate (Tarea 8 — auth
     * del portal). Solo permite el acceso al panel 'clientes' cuando el
     * cliente tiene portal_acceso=activo y estado=activo.
     *
     * Para otros paneles (admin, etc.) retorna false — los clientes no
     * son staff.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() !== 'clientes') {
            return false;
        }

        return $this->portal_acceso?->value === PortalAccesoEnum::ACTIVO->value
            && $this->estado === ClienteEstado::ACTIVO;
    }

    /**
     * Nombre que Filament muestra en el user-menu y la topbar.
     *
     * Sin este contrato Filament intenta leer el atributo 'name'
     * (que no existe en la tabla clientes) y lanza
     * TypeError en Filament\FilamentManager::getUserName().
     */
    public function getFilamentName(): string
    {
        return (string) ($this->nombre ?? $this->email ?? 'Cliente');
    }
}
