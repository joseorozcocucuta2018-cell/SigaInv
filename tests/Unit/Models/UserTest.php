<?php

/*
|--------------------------------------------------------------------------
| UserTest.php — Tests unitarios del modelo User
| v2 — RefreshDatabase via Pest.php (MySQL sigainv_test)
|--------------------------------------------------------------------------
*/

use App\Enums\UserEstado;
use App\Models\User;
use Filament\Panel;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;

describe('Modelo User', function () {

    // ── Estructura ────────────────────────────────────────────────────────────

    it('tiene los campos fillable correctos', function () {
        $user = new User;

        expect($user->getFillable())
            ->toContain('name')
            ->toContain('email')
            ->toContain('password')
            ->toContain('celular')
            ->toContain('fecha_nacimiento')
            ->toContain('cargo')
            ->toContain('avatar')
            ->toContain('password_changed_at');
    });

    it('castea estado como UserEstado enum', function () {
        $user = User::factory()->create(['estado' => UserEstado::ACTIVO]);
        expect($user->estado)->toBeInstanceOf(UserEstado::class)->toBe(UserEstado::ACTIVO);
    });

    it('castea fecha_nacimiento como date', function () {
        $user = User::factory()->create(['fecha_nacimiento' => '1990-05-15']);
        expect($user->fecha_nacimiento)->toBeInstanceOf(Carbon::class);
    });

    it('castea password_changed_at como datetime', function () {
        $user = User::factory()->create(['password_changed_at' => now()]);
        expect($user->password_changed_at)->toBeInstanceOf(Carbon::class);
    });

    // ── canAccessPanel ────────────────────────────────────────────────────────

    it('permite acceso al panel si está activo y tiene rol válido', function () {
        $user = crearUsuarioConRol('administrador');
        $panel = createMockPanel('admin');
        expect($user->canAccessPanel($panel))->toBeTrue();
    });

    it('deniega acceso al panel si está inactivo', function () {
        Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
        $user = User::factory()->create(['estado' => UserEstado::INACTIVO]);
        $user->assignRole('administrador');
        $panel = createMockPanel('admin');
        expect($user->canAccessPanel($panel))->toBeFalse();
    });

    it('deniega acceso al panel si no tiene rol válido', function () {
        $user = User::factory()->create(['estado' => UserEstado::ACTIVO]);
        $panel = createMockPanel('admin');
        expect($user->canAccessPanel($panel))->toBeFalse();
    });

    it('permite acceso a auxiliar, contador y vendedor', function () {
        $panel = createMockPanel('admin');
        foreach (['auxiliar', 'contador', 'vendedor'] as $rol) {
            $user = crearUsuarioConRol($rol);
            expect($user->canAccessPanel($panel))->toBeTrue("El rol '$rol' debería tener acceso");
        }
    });

    // ── Avatar ────────────────────────────────────────────────────────────────

    it('retorna null como avatar url si no tiene avatar', function () {
        $user = User::factory()->create(['avatar' => null]);
        expect($user->getFilamentAvatarUrl())->toBeNull();
    });

    // ── Roles ─────────────────────────────────────────────────────────────────

    it('puede asignar y verificar un rol', function () {
        $user = crearUsuarioConRol('contador');
        expect($user->hasRole('contador'))->toBeTrue();
    });

    it('puede tener múltiples roles', function () {
        Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'contador',      'guard_name' => 'web']);

        $user = User::factory()->create(['estado' => UserEstado::ACTIVO]);
        $user->assignRole(['administrador', 'contador']);

        expect($user->hasRole('administrador'))->toBeTrue()
            ->and($user->hasRole('contador'))->toBeTrue();
    });
});

function createMockPanel(string $id): Panel
{
    return new class($id) extends Panel
    {
        public function __construct(private string $panelId) {}

        public function getId(): string
        {
            return $this->panelId;
        }
    };
}
