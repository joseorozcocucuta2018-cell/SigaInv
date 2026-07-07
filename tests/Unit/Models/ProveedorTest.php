<?php

/*
|--------------------------------------------------------------------------
| ProveedorTest.php — Tests unitarios del modelo Proveedor
| v2 — columnas corregidas: departamento_id, ciudad_id, usuario_id
|--------------------------------------------------------------------------
*/

use App\Enums\ProveedorEstado;
use App\Models\Ciudad;
use App\Models\Departamento;
use App\Models\Proveedor;
use App\Models\User;

describe('Modelo Proveedor', function () {

    // ── Estructura ────────────────────────────────────────────────────────────

    it('tiene los campos fillable correctos', function () {
        expect((new Proveedor)->getFillable())
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

    it('castea estado como ProveedorEstado enum', function () {
        $proveedor = Proveedor::factory()->create(['estado' => ProveedorEstado::ACTIVO]);
        expect($proveedor->estado)->toBeInstanceOf(ProveedorEstado::class)->toBe(ProveedorEstado::ACTIVO);
    });

    it('castea saldo como decimal', function () {
        $proveedor = Proveedor::factory()->create(['saldo' => 2500.75]);
        expect((float) $proveedor->saldo)->toEqual(2500.75);
    });

    // ── Relaciones ────────────────────────────────────────────────────────────

    it('pertenece a un departamento', function () {
        $depto = Departamento::factory()->create(['nombre' => 'Cundinamarca']);
        $ciudad = Ciudad::factory()->create(['departamento_id' => $depto->id]);
        $proveedor = Proveedor::factory()->create([
            'departamento_id' => $depto->id,
            'ciudad_id' => $ciudad->id,
        ]);

        expect($proveedor->departamento)->toBeInstanceOf(Departamento::class)
            ->and($proveedor->departamento->nombre)->toBe('Cundinamarca');
    });

    it('pertenece a una ciudad', function () {
        $depto = Departamento::factory()->create();
        $ciudad = Ciudad::factory()->create([
            'nombre' => 'Bogotá',
            'departamento_id' => $depto->id,
        ]);
        $proveedor = Proveedor::factory()->create([
            'departamento_id' => $depto->id,
            'ciudad_id' => $ciudad->id,
        ]);

        expect($proveedor->ciudad)->toBeInstanceOf(Ciudad::class)
            ->and($proveedor->ciudad->nombre)->toBe('Bogotá');
    });

    it('puede pertenecer a un usuario registrador', function () {
        $usuario = crearUsuarioConRol('administrador');
        $proveedor = Proveedor::factory()->create(['usuario_id' => $usuario->id]);

        expect($proveedor->usuario)->toBeInstanceOf(User::class)
            ->and($proveedor->usuario->id)->toBe($usuario->id);
    });

    // ── Regla de negocio ──────────────────────────────────────────────────────

    it('el primer registro insertado tiene id 1 (PROVEEDORES VARIOS)', function () {
        $proveedor = Proveedor::factory()->create(['nombre' => 'PROVEEDORES VARIOS']);
        expect($proveedor->id)->toBe(1)
            ->and($proveedor->nombre)->toBe('PROVEEDORES VARIOS');
    });
});
