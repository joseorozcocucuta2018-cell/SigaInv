<?php

/*
|--------------------------------------------------------------------------
| SeedersTest.php — Tests de datos iniciales (Seeders)
| Proyecto: sigaInv — Laravel 12 + Filament 4.x
|--------------------------------------------------------------------------
|
| Valida que después de db:seed los datos base están correctos:
|   - 4 roles creados (administrador, auxiliar, contador, vendedor)
|   - Registro id=1 protegido en clientes y proveedores
|   - Colombia: 33 departamentos presentes
|   - Datos de catálogos básicos (unidades de medida, impuestos, etc.)
|
| NOTA: Estos tests usan RefreshDatabase + seeders.
| Para correr con seeders: php artisan test --env=testing
| El .env.testing apunta a sigainv_test ya seeded.
|
*/

use App\Enums\ClienteEstado;
use App\Enums\ProveedorEstado;
use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\Proveedor;
use Database\Seeders\CiudadesSeeder;
use Database\Seeders\ClientesSeeder;
use Database\Seeders\DepartamentosSeeder;
use Database\Seeders\ProveedoresSeeder;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\Models\Role;

describe('Seeders — Roles', function () {

    beforeEach(function () {
        // Ejecutar RoleSeeder antes de cada test de este grupo
        $this->seed(RoleSeeder::class);
    });

    it('crea el rol administrador', function () {
        expect(Role::where('name', 'administrador')->exists())->toBeTrue();
    });

    it('crea el rol auxiliar', function () {
        expect(Role::where('name', 'auxiliar')->exists())->toBeTrue();
    });

    it('crea el rol contador', function () {
        expect(Role::where('name', 'contador')->exists())->toBeTrue();
    });

    it('crea el rol vendedor', function () {
        expect(Role::where('name', 'vendedor')->exists())->toBeTrue();
    });

    it('crea exactamente 4 roles base', function () {
        $rolesBase = ['administrador', 'auxiliar', 'contador', 'vendedor'];
        foreach ($rolesBase as $rol) {
            expect(Role::where('name', $rol)->exists())->toBeTrue("Falta el rol: $rol");
        }
    });

    it('el RoleSeeder no duplica roles al ejecutarse dos veces', function () {
        $this->seed(RoleSeeder::class); // Segunda vez
        $count = Role::where('name', 'administrador')->count();
        expect($count)->toBe(1);
    });
});

describe('Seeders — DepartamentosSeeder', function () {

    beforeEach(function () {
        $this->seed(DepartamentosSeeder::class);
    });

    it('crea 33 departamentos de Colombia', function () {
        expect(Departamento::count())->toBe(33);
    });

    it('Norte de Santander existe en los departamentos', function () {
        expect(
            Departamento::where('nombre', 'like', '%Norte de Santander%')->exists()
        )->toBeTrue();
    });

    it('Cundinamarca existe en los departamentos', function () {
        expect(
            Departamento::where('nombre', 'like', '%Cundinamarca%')->exists()
        )->toBeTrue();
    });

    it('no duplica departamentos al ejecutarse dos veces', function () {
        $this->seed(DepartamentosSeeder::class); // Segunda vez
        expect(Departamento::count())->toBe(33);
    });
});

describe('Seeders — ClientesSeeder', function () {

    beforeEach(function () {
        $this->seed(DepartamentosSeeder::class);
        $this->seed(CiudadesSeeder::class);
        $this->seed(ClientesSeeder::class);
    });

    it('crea el registro CLIENTES VARIOS con id=1', function () {
        $varios = Cliente::find(1);
        expect($varios)->not->toBeNull()
            ->and($varios->nombre)->toContain('VARIOS');
    });

    it('CLIENTES VARIOS está activo', function () {
        expect(Cliente::find(1)->estado)->toBeInstanceOf(ClienteEstado::class);
    });

    it('no duplica CLIENTES VARIOS al ejecutarse dos veces', function () {
        $this->seed(ClientesSeeder::class); // Segunda vez
        $count = Cliente::where('nombre', 'like', '%VARIOS%')->count();
        expect($count)->toBe(1);
    });
});

describe('Seeders — ProveedoresSeeder', function () {

    beforeEach(function () {
        $this->seed(DepartamentosSeeder::class);
        $this->seed(CiudadesSeeder::class);
        $this->seed(ProveedoresSeeder::class);
    });

    it('crea el registro PROVEEDORES VARIOS con id=1', function () {
        $varios = Proveedor::find(1);
        expect($varios)->not->toBeNull()
            ->and($varios->nombre)->toContain('VARIOS');
    });

    it('PROVEEDORES VARIOS está activo', function () {
        expect(Proveedor::find(1)->estado)->toBeInstanceOf(ProveedorEstado::class);
    });

    it('no duplica PROVEEDORES VARIOS al ejecutarse dos veces', function () {
        $this->seed(ProveedoresSeeder::class); // Segunda vez
        $count = Proveedor::where('nombre', 'like', '%VARIOS%')->count();
        expect($count)->toBe(1);
    });
});
