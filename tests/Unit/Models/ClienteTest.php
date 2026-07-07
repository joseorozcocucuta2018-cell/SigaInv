<?php

/*
|--------------------------------------------------------------------------
| ClienteTest.php — Tests unitarios del modelo Cliente
| v2 — columnas corregidas: departamento_id, ciudad_id, usuario_id
|--------------------------------------------------------------------------
*/

use App\Enums\ClienteEstado;
use App\Enums\PortalAccesoEnum;
use App\Models\Ciudad;
use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\User;

describe('Modelo Cliente', function () {

    // ── Estructura ────────────────────────────────────────────────────────────

    it('tiene los campos fillable correctos', function () {
        expect((new Cliente)->getFillable())
            ->toContain('nombre')
            ->toContain('documento')
            ->toContain('tipo_documento')
            ->toContain('telefono')
            ->toContain('email')
            ->toContain('estado')
            ->toContain('ciudad_id')
            ->toContain('departamento_id')
            ->toContain('usuario_id');
    });

    it('castea estado como ClienteEstado enum', function () {
        $cliente = Cliente::factory()->create(['estado' => ClienteEstado::ACTIVO]);
        expect($cliente->estado)->toBeInstanceOf(ClienteEstado::class)->toBe(ClienteEstado::ACTIVO);
    });

    it('castea portal_acceso como PortalAccesoEnum', function () {
        $cliente = Cliente::factory()->create(['portal_acceso' => PortalAccesoEnum::ACTIVO]);
        expect($cliente->portal_acceso)
            ->toBeInstanceOf(PortalAccesoEnum::class)
            ->toBe(PortalAccesoEnum::ACTIVO);
    });

    it('puede asignar todos los valores de PortalAccesoEnum', function (PortalAccesoEnum $valor) {
        $cliente = Cliente::factory()->create(['portal_acceso' => $valor]);
        expect($cliente->portal_acceso)->toBe($valor);
    })->with([
        'sin_acceso' => [PortalAccesoEnum::SIN_ACCESO],
        'pendiente' => [PortalAccesoEnum::PENDIENTE],
        'activo' => [PortalAccesoEnum::ACTIVO],
    ]);

    it('castea saldo como decimal', function () {
        $cliente = Cliente::factory()->create(['saldo' => 1500.50]);
        expect((float) $cliente->saldo)->toEqual(1500.50);
    });

    // ── Relaciones ────────────────────────────────────────────────────────────

    it('pertenece a un departamento', function () {
        $depto = Departamento::factory()->create(['nombre' => 'Norte de Santander']);
        $ciudad = Ciudad::factory()->create(['departamento_id' => $depto->id]);
        $cliente = Cliente::factory()->create([
            'departamento_id' => $depto->id,
            'ciudad_id' => $ciudad->id,
        ]);

        expect($cliente->departamento)->toBeInstanceOf(Departamento::class)
            ->and($cliente->departamento->nombre)->toBe('Norte de Santander');
    });

    it('pertenece a una ciudad', function () {
        $depto = Departamento::factory()->create();
        $ciudad = Ciudad::factory()->create([
            'nombre' => 'Cúcuta',
            'departamento_id' => $depto->id,
        ]);
        $cliente = Cliente::factory()->create([
            'departamento_id' => $depto->id,
            'ciudad_id' => $ciudad->id,
        ]);

        expect($cliente->ciudad)->toBeInstanceOf(Ciudad::class)
            ->and($cliente->ciudad->nombre)->toBe('Cúcuta');
    });

    it('puede pertenecer a un usuario registrador', function () {
        $usuario = crearUsuarioConRol('administrador');
        $cliente = Cliente::factory()->create(['usuario_id' => $usuario->id]);

        expect($cliente->usuario)->toBeInstanceOf(User::class)
            ->and($cliente->usuario->id)->toBe($usuario->id);
    });

    // ── Regla de negocio ──────────────────────────────────────────────────────

    it('el primer registro insertado tiene id 1', function () {
        $cliente = Cliente::factory()->create(['nombre' => 'CLIENTES VARIOS']);
        expect($cliente->id)->toBe(1)
            ->and($cliente->nombre)->toBe('CLIENTES VARIOS');
    });
});
