<?php

declare(strict_types=1);

use App\Enums\CajaEstado;
use App\Enums\TurnoEstado;
use App\Enums\UserEstado;
use App\Models\Bodega;
use App\Models\Caja;
use App\Models\Ciudad;
use App\Models\Departamento;
use App\Models\MovimientoCaja;
use App\Models\Turno;
use App\Models\User;
use App\Services\TurnoService;
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

    $depto = Departamento::create(['nombre' => 'Depto Turno']);
    $ciudad = Ciudad::create(['nombre' => 'Ciudad Turno', 'departamento_id' => $depto->id]);
    $this->bodega = Bodega::create([
        'nombre' => 'Bodega Turno',
        'direccion1' => 'Calle 1',
        'departamento_id' => $depto->id,
        'ciudad_id' => $ciudad->id,
        'activo' => true,
    ]);
    $this->caja = Caja::create([
        'nombre' => 'Caja Turno',
        'saldo_inicial' => 0,
        'activo' => true,
        'estado' => CajaEstado::ACTIVA,
        'usuario_id' => $this->user->id,
    ]);
});

it('TurnoService::abrir crea un turno abierto con saldo_inicial', function () {
    $turno = TurnoService::abrir($this->user, [
        'caja_id' => $this->caja->id,
        'bodega_id' => $this->bodega->id,
        'saldo_inicial' => 100000,
    ]);

    expect($turno)->toBeInstanceOf(Turno::class);
    expect($turno->estado)->toBe(TurnoEstado::ABIERTO);
    expect((float) $turno->saldo_inicial)->toBe(100000.0);
    expect($turno->caja_id)->toBe($this->caja->id);
    expect($turno->bodega_id)->toBe($this->bodega->id);
    expect($turno->usuario_id)->toBe($this->user->id);
});

it('TurnoService::abrir rechaza si el usuario ya tiene un turno abierto', function () {
    TurnoService::abrir($this->user, [
        'caja_id' => $this->caja->id,
        'bodega_id' => $this->bodega->id,
        'saldo_inicial' => 50000,
    ]);

    expect(fn () => TurnoService::abrir($this->user, [
        'caja_id' => $this->caja->id,
        'saldo_inicial' => 10000,
    ]))->toThrow(InvalidArgumentException::class, 'ya tiene un turno abierto');
});

it('TurnoService::abrir rechaza saldo_inicial negativo', function () {
    expect(fn () => TurnoService::abrir($this->user, [
        'caja_id' => $this->caja->id,
        'saldo_inicial' => -1,
    ]))->toThrow(InvalidArgumentException::class, 'no puede ser negativo');
});

it('TurnoService::abrir rechaza caja inexistente', function () {
    expect(fn () => TurnoService::abrir($this->user, [
        'caja_id' => 9999,
        'saldo_inicial' => 0,
    ]))->toThrow(InvalidArgumentException::class, 'caja no existe');
});

it('TurnoService::getActivo retorna null si no hay turno', function () {
    [$turno, $data] = TurnoService::getActivo($this->user);

    expect($turno)->toBeNull();
    expect($data['ingresos_acumulados'])->toBe(0.0);
    expect($data['saldo_esperado_actual'])->toBe(0.0);
    expect($data['ventas_count'])->toBe(0);
    expect($data['ventas_total'])->toBe(0.0);
    expect($data['desglose_pagos'])->toBe([]);
});

it('TurnoService::getActivo retorna el turno y recalcula ingresos desde MovimientoCaja', function () {
    $turno = TurnoService::abrir($this->user, [
        'caja_id' => $this->caja->id,
        'bodega_id' => $this->bodega->id,
        'saldo_inicial' => 100000,
    ]);

    MovimientoCaja::create([
        'caja_id' => $this->caja->id,
        'usuario_id' => $this->user->id,
        'forma_pago_id' => null,
        'fecha_movimiento' => now()->addMinute(),
        'tipo' => 'ingreso',
        'monto' => 50000,
        'saldo_actual' => 150000,
        'concepto' => 'Test ingreso',
    ]);

    [$current, $data] = TurnoService::getActivo($this->user);

    expect($current->id)->toBe($turno->id);
    expect($data['ingresos_acumulados'])->toBe(50000.0);
    expect($data['saldo_esperado_actual'])->toBe(150000.0);
});

it('TurnoService::cerrar recalcula el esperado desde MovimientoCaja y registra diferencia', function () {
    TurnoService::abrir($this->user, [
        'caja_id' => $this->caja->id,
        'bodega_id' => $this->bodega->id,
        'saldo_inicial' => 100000,
    ]);

    MovimientoCaja::create([
        'caja_id' => $this->caja->id,
        'usuario_id' => $this->user->id,
        'fecha_movimiento' => now()->addMinute(),
        'tipo' => 'ingreso',
        'monto' => 50000,
        'saldo_actual' => 150000,
        'concepto' => 'Venta test',
    ]);

    [$turno, $data] = TurnoService::cerrar($this->user, 145000.0);

    expect($turno->estado)->toBe(TurnoEstado::CERRADO);
    expect((float) $turno->saldo_final_esperado)->toBe(150000.0);
    expect((float) $turno->saldo_final_real)->toBe(145000.0);
    expect((float) $turno->diferencia)->toBe(-5000.0);
    expect($data['desglose_pagos'])->toBeArray();
});

it('TurnoService::cerrar sin turno abierto lanza excepción', function () {
    expect(fn () => TurnoService::cerrar($this->user, 0.0))
        ->toThrow(InvalidArgumentException::class, 'turno abierto');
});

it('TurnoService::cerrar con saldo negativo lanza excepción', function () {
    expect(fn () => TurnoService::cerrar($this->user, -1.0))
        ->toThrow(InvalidArgumentException::class, 'no puede ser negativo');
});
