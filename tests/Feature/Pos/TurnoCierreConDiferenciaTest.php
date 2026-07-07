<?php

declare(strict_types=1);

use App\Enums\CajaEstado;
use App\Enums\UserEstado;
use App\Models\Bodega;
use App\Models\Caja;
use App\Models\Ciudad;
use App\Models\Departamento;
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

    $depto = Departamento::create(['nombre' => 'Depto']);
    $ciudad = Ciudad::create(['nombre' => 'Ciudad', 'departamento_id' => $depto->id]);
    $bodega = Bodega::create(['nombre' => 'Bodega', 'direccion1' => 'Calle', 'departamento_id' => $depto->id, 'ciudad_id' => $ciudad->id, 'activo' => true]);
    $this->caja = Caja::create(['nombre' => 'Caja', 'saldo_inicial' => 0, 'activo' => true, 'estado' => CajaEstado::ACTIVA, 'usuario_id' => $this->user->id]);

    $this->withToken($this->token)->postJson('/pos/api/turnos', [
        'caja_id' => $this->caja->id,
        'bodega_id' => $bodega->id,
        'saldo_inicial' => 100000,
    ]);
});

it('cierra turno con diferencia positiva registrada', function () {
    $response = $this->withToken($this->token)->postJson('/pos/api/turnos/cerrar', [
        'saldo_final_real' => 105000,
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('turnos', [
        'usuario_id' => $this->user->id,
        'saldo_final_real' => 105000,
        'diferencia' => 5000,
    ]);
});

it('cierra turno con diferencia negativa registrada', function () {
    $response = $this->withToken($this->token)->postJson('/pos/api/turnos/cerrar', [
        'saldo_final_real' => 95000,
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('turnos', [
        'usuario_id' => $this->user->id,
        'diferencia' => -5000,
    ]);
});
