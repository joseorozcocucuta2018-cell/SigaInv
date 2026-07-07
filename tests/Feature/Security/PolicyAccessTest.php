<?php

use App\Enums\UserEstado;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| PolicyAccessTest
|
| Verifica las reglas de acceso al panel Filament.
|
| NOTA: Se ejecuta RoleSeeder antes de cada test porque assignRole()
| requiere que el rol exista en la BD. Con RefreshDatabase la BD se
| limpia entre tests — el beforeEach recrea los roles.
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

function crearUsuarioPolicyTest2(UserEstado $estado = UserEstado::ACTIVO): User
{
    $user = User::create([
        'name' => 'Usuario Test',
        'email' => 'policy-'.uniqid().'@test.com',
        'password' => bcrypt('password'),
        'estado' => $estado,
    ]);

    return $user;
}

// ── Lógica del modelo ─────────────────────────────────────────────────────────

it('usuario sin rol no puede acceder al panel', function () {
    $user = crearUsuarioPolicyTest2(estado: UserEstado::ACTIVO);

    expect($user->estado)->toBe(UserEstado::ACTIVO);
    expect($user->hasAnyRole(['administrador', 'auxiliar', 'contador', 'vendedor']))->toBeFalse();
});

it('usuario inactivo con rol no puede acceder al panel', function () {
    $user = crearUsuarioPolicyTest2(estado: UserEstado::INACTIVO);
    $user->assignRole('administrador');

    expect($user->estado)->toBe(UserEstado::INACTIVO);
    expect(
        $user->estado === UserEstado::ACTIVO && $user->hasAnyRole(['administrador', 'auxiliar', 'contador', 'vendedor'])
    )->toBeFalse();
});

it('administrador activo puede acceder al panel', function () {
    $user = crearUsuarioPolicyTest2(estado: UserEstado::ACTIVO);
    $user->assignRole('administrador');

    expect($user->estado)->toBe(UserEstado::ACTIVO);
    expect($user->hasAnyRole(['administrador', 'auxiliar', 'contador', 'vendedor']))->toBeTrue();
});

it('auxiliar activo puede acceder al panel', function () {
    $user = crearUsuarioPolicyTest2(estado: UserEstado::ACTIVO);
    $user->assignRole('auxiliar');

    expect(
        $user->estado === UserEstado::ACTIVO && $user->hasAnyRole(['administrador', 'auxiliar', 'contador', 'vendedor'])
    )->toBeTrue();
});

// ── Acceso real vía HTTP ──────────────────────────────────────────────────────

it('usuario sin rol es redirigido al acceder al panel', function () {
    $user = crearUsuarioPolicyTest2(estado: UserEstado::ACTIVO);

    $response = $this->actingAs($user)->get('/admin');

    expect($response->status())->toBeIn([302, 403]);
});

it('usuario inactivo es bloqueado al acceder al panel', function () {
    $user = crearUsuarioPolicyTest2(estado: UserEstado::INACTIVO);
    $user->assignRole('administrador');

    $response = $this->actingAs($user)->get('/admin');

    expect($response->status())->toBeIn([302, 403]);
});
