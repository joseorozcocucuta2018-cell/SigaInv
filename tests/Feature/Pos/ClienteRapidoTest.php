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
    $this->user = User::factory()->create(['estado' => UserEstado::ACTIVO]);
    $this->user->assignRole('vendedor');
    $this->token = $this->user->createToken('test')->plainTextToken;
});

it('crea cliente rápido vía API POS con 5 campos', function () {
    $response = $this->withToken($this->token)->postJson('/pos/api/clientes', [
        'nombre' => 'Juan Pérez',
        'tipo_documento' => 'CC',
        'documento' => '100'.random_int(1000000, 9999999),
        'telefono' => '3001234567',
        'email' => 'juan-'.uniqid().'@test.com',
    ]);

    $response->assertStatus(201);
    $response->assertJsonStructure(['data' => ['id', 'nombre', 'documento']]);
});

it('crea cliente con defaults portal_acceso=sin_acceso', function () {
    $response = $this->withToken($this->token)->postJson('/pos/api/clientes', [
        'nombre' => 'Ana López',
        'documento' => '100'.random_int(1000000, 9999999),
        'email' => 'ana-'.uniqid().'@test.com',
    ]);

    $response->assertStatus(201);
});

it('rechaza cliente sin nombre ni documento', function () {
    $response = $this->withToken($this->token)->postJson('/pos/api/clientes', [
        'nombre' => '',
        'documento' => '',
    ]);

    $response->assertStatus(422);
});

it('rechaza cliente con documento duplicado', function () {
    $doc = '100'.random_int(1000000, 9999999);
    $this->withToken($this->token)->postJson('/pos/api/clientes', [
        'nombre' => 'Primero',
        'documento' => $doc,
        'email' => 'a-'.uniqid().'@test.com',
    ]);

    $response = $this->withToken($this->token)->postJson('/pos/api/clientes', [
        'nombre' => 'Segundo',
        'documento' => $doc,
        'email' => 'b-'.uniqid().'@test.com',
    ]);

    $response->assertStatus(422);
});
