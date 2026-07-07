<?php

/*
|--------------------------------------------------------------------------
| Pest.php — Configuración global de Pest PHP
| Proyecto: sigaInv — Laravel 12 + Filament 4.x
| v2 — RefreshDatabase en Unit y Feature (MySQL sigainv_test)
|--------------------------------------------------------------------------
*/

use App\Enums\UserEstado;
use App\Models\Categoria;
use App\Models\Impuesto;
use App\Models\Marca;
use App\Models\UnidadMedida;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

// TestCase base por carpeta
uses(TestCase::class)->in('Feature');
uses(TestCase::class)->in('Unit');

// RefreshDatabase en AMBAS carpetas → usa MySQL sigainv_test (.env.testing)
uses(RefreshDatabase::class)->in('Feature');
uses(RefreshDatabase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Helper: crearUsuarioConRol($rol)
|--------------------------------------------------------------------------
*/
function crearUsuarioConRol(string $rol): User
{
    // Ejecutar RoleSeeder para crear roles Y permisos
    if (Role::count() === 0) {
        (new RoleSeeder)->run();
        // Limpiar cache de permisos de Spatie
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    $user = User::factory()->create(['estado' => UserEstado::ACTIVO]);
    $user->assignRole($rol);

    return $user;
}

/*
|--------------------------------------------------------------------------
| Helper: loginComoAdmin()
|--------------------------------------------------------------------------
*/
function loginComoAdmin(): User
{
    $user = crearUsuarioConRol('administrador');
    test()->actingAs($user);

    return $user;
}

/*
|--------------------------------------------------------------------------
| Helper: loginComoRol($rol)
|--------------------------------------------------------------------------
*/
function loginComoRol(string $rol): User
{
    $user = crearUsuarioConRol($rol);
    test()->actingAs($user);

    return $user;
}

/*
|--------------------------------------------------------------------------
| Helper: crearReferenciasProducto()
| Crea registros mínimos en tablas referenciales para crear productos.
|--------------------------------------------------------------------------
*/
function crearReferenciasProducto(): object
{
    $marca = Marca::firstOrCreate(['nombre' => 'MARCA PROPIA'], ['activo' => true]);
    $categoria = Categoria::firstOrCreate(['nombre' => 'GENERAL'], ['activo' => true]);
    $impuesto = Impuesto::firstOrCreate(
        ['nombre' => 'EXENTO', 'porcentaje' => 0],
        ['porcentaje' => 0],
    );
    $unidad = UnidadMedida::firstOrCreate(
        ['nombre' => 'UNIDAD'],
        ['simbolo' => 'UN', 'activo' => true],
    );

    return (object) compact('marca', 'categoria', 'impuesto', 'unidad');
}
