<?php

use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\Proveedor;
use Database\Seeders\CiudadesSeeder;
use Database\Seeders\ClientesSeeder;
use Database\Seeders\DepartamentosSeeder;
use Database\Seeders\ProveedoresSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| SeedersIdempotencyExtendedTest
|
| Verifica que los seeders son idempotentes: ejecutarlos múltiples veces
| no duplica datos.
|
| NOTA: Se usa $this->seed() en lugar de new Seeder()->run() porque algunos
| seeders llaman $this->command->info() que es null fuera de artisan.
| $this->seed() inicializa el seeder correctamente con el contexto de testing.
|--------------------------------------------------------------------------
*/

it('ejecutar RoleSeeder tres veces no duplica roles', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(RoleSeeder::class);
    $this->seed(RoleSeeder::class);

    expect(Role::count())->toBeLessThanOrEqual(10);
});

it('ejecutar RoleSeeder dos veces produce exactamente los mismos roles', function () {
    $this->seed(RoleSeeder::class);
    $count1 = Role::count();

    $this->seed(RoleSeeder::class);
    $count2 = Role::count();

    expect($count1)->toBe($count2);
});

it('ejecutar DepartamentosSeeder dos veces no duplica departamentos', function () {
    $this->seed(DepartamentosSeeder::class);
    $count1 = Departamento::count();

    $this->seed(DepartamentosSeeder::class);
    $count2 = Departamento::count();

    expect($count1)->toBe($count2);
    expect($count1)->toBe(33);
});

it('ejecutar ClientesSeeder dos veces no duplica CLIENTES VARIOS', function () {
    $this->seed(DepartamentosSeeder::class);
    $this->seed(CiudadesSeeder::class);
    $this->seed(ClientesSeeder::class);
    $this->seed(ClientesSeeder::class);

    $count = Cliente::where('nombre', 'CLIENTES VARIOS')->count();
    expect($count)->toBe(1);
});

it('ejecutar ProveedoresSeeder dos veces no duplica PROVEEDORES VARIOS', function () {
    $this->seed(DepartamentosSeeder::class);
    $this->seed(CiudadesSeeder::class);
    $this->seed(ProveedoresSeeder::class);
    $this->seed(ProveedoresSeeder::class);

    $count = Proveedor::where('nombre', 'PROVEEDORES VARIOS')->count();
    expect($count)->toBe(1);
});
