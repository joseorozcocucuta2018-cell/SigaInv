<?php

declare(strict_types=1);

use App\Enums\ClienteEstado;
use App\Enums\UserEstado;
use App\Models\AuditoriaDocumento;
use App\Models\Cliente;
use App\Models\User;
use App\Services\ClienteService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    if (Role::count() === 0) {
        (new RoleSeeder)->run();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
    DB::table('departamentos')->insert(['id' => 54, 'nombre' => 'Norte de Santander']);
    DB::table('ciudades')->insert(['id' => 889, 'nombre' => 'Cúcuta', 'departamento_id' => 54]);
    $this->user = User::factory()->create(['estado' => UserEstado::ACTIVO]);
    $this->user->assignRole('vendedor');
});

it('ClienteService::crearRapido crea cliente con defaults Cúcuta', function () {
    $cliente = ClienteService::crearRapido([
        'nombre' => 'juan pérez',
        'tipo_documento' => 'CC',
        'documento' => '1090'.random_int(100000, 999999),
        'telefono' => '3001234567',
        'correo' => 'juan-'.uniqid().'@test.com',
    ]);

    expect($cliente)->toBeInstanceOf(Cliente::class);
    expect($cliente->nombre)->toBe('JUAN PÉREZ');
    expect($cliente->estado)->toBe(ClienteEstado::ACTIVO);
    expect($cliente->portal_acceso->value)->toBe('sin_acceso');
    expect($cliente->direccion1)->toBe('-');
    expect($cliente->departamento_id)->toBe(54);
    expect($cliente->ciudad_id)->toBe(889);
});

it('ClienteService::crearRapido exige los 5 campos requeridos', function () {
    expect(fn () => ClienteService::crearRapido([
        'nombre' => 'X',
        'tipo_documento' => 'CC',
    ]))->toThrow(ValidationException::class);
});

it('ClienteService::crearRapido rechaza documento duplicado', function () {
    $doc = '1090'.random_int(100000, 999999);
    ClienteService::crearRapido([
        'nombre' => 'A',
        'tipo_documento' => 'CC',
        'documento' => $doc,
        'telefono' => '300',
        'correo' => 'a-'.uniqid().'@test.com',
    ]);

    expect(fn () => ClienteService::crearRapido([
        'nombre' => 'B',
        'tipo_documento' => 'CC',
        'documento' => $doc,
        'telefono' => '301',
        'correo' => 'b-'.uniqid().'@test.com',
    ]))->toThrow(ValidationException::class, 'documento');
});

it('ClienteService::crearRapido registra auditoría pos.cliente.crear', function () {
    $cliente = ClienteService::crearRapido([
        'nombre' => 'Ana',
        'tipo_documento' => 'CC',
        'documento' => '1090'.random_int(100000, 999999),
        'telefono' => '300',
        'correo' => 'ana-'.uniqid().'@test.com',
    ]);

    $aud = AuditoriaDocumento::where('documento_tipo', 'cliente')
        ->where('documento_id', $cliente->id)
        ->where('accion', 'pos.cliente.crear')->first();
    expect($aud)->not->toBeNull();
});
