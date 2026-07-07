<?php

declare(strict_types=1);

use App\Enums\UserEstado;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    if (Role::count() === 0) {
        (new RoleSeeder)->run();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
});

it('login POS ok con credenciales válidas y rol vendedor', function () {
    $user = User::factory()->create(['estado' => UserEstado::ACTIVO, 'password' => bcrypt('secret')]);
    $user->assignRole('vendedor');

    $response = $this->postJson('/pos/api/auth/login', [
        'email' => $user->email,
        'password' => 'secret',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'roles']]);
});

it('login POS falla con credenciales inválidas', function () {
    $user = User::factory()->create(['estado' => UserEstado::ACTIVO, 'password' => bcrypt('secret')]);
    $user->assignRole('vendedor');

    $response = $this->postJson('/pos/api/auth/login', [
        'email' => $user->email,
        'password' => 'wrong',
    ]);

    $response->assertStatus(422);
});

it('login POS falla si usuario no tiene rol vendedor/administrador', function () {
    $user = User::factory()->create(['estado' => UserEstado::ACTIVO, 'password' => bcrypt('secret')]);
    $user->assignRole('auxiliar');

    $response = $this->postJson('/pos/api/auth/login', [
        'email' => $user->email,
        'password' => 'secret',
    ]);

    $response->assertStatus(422);
});

it('endpoint me retorna 401 sin token', function () {
    $this->getJson('/pos/api/auth/me')->assertStatus(401);
});

it('endpoint me retorna datos con token válido', function () {
    $user = User::factory()->create(['estado' => UserEstado::ACTIVO]);
    $user->assignRole('vendedor');
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withToken($token)->getJson('/pos/api/auth/me');

    $response->assertStatus(200);
    $response->assertJsonPath('email', $user->email);
});
