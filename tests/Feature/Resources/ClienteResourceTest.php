<?php

/*
|--------------------------------------------------------------------------
| ClienteResourceTest.php — Tests funcionales del ClienteResource
| v2 — columnas corregidas: departamento_id, ciudad_id
|--------------------------------------------------------------------------
*/

use App\Enums\ClienteEstado;
use App\Filament\Resources\ClienteResource;
use App\Models\Ciudad;
use App\Models\Cliente;
use App\Models\Departamento;

use function Pest\Livewire\livewire;

// Helper local: crea departamento + ciudad
function crearUbicacion(): array
{
    $depto = Departamento::factory()->create(['nombre' => 'Norte de Santander']);
    $ciudad = Ciudad::factory()->create([
        'nombre' => 'Cúcuta',
        'departamento_id' => $depto->id,
    ]);

    return [$depto, $ciudad];
}

describe('ClienteResource — Listado', function () {

    it('el administrador puede ver la lista de clientes', function () {
        loginComoAdmin();

        livewire(ClienteResource\Pages\ListClientes::class)
            ->assertSuccessful();
    });

    it('muestra clientes existentes en la tabla', function () {
        loginComoAdmin();
        Cliente::factory()->create(['nombre' => 'Empresa ABC S.A.S']);

        livewire(ClienteResource\Pages\ListClientes::class)
            ->assertSee('EMPRESA ABC S.A.S');
    });
});

describe('ClienteResource — Crear', function () {

    it('el administrador puede crear un cliente nuevo', function () {
        loginComoAdmin();
        [$depto, $ciudad] = crearUbicacion();

        livewire(ClienteResource\Pages\CreateCliente::class)
            ->fillForm([
                'nombre' => 'Cliente Test SAS',
                'documento' => '900123456',
                'tipo_documento' => 'NIT',
                'telefono' => '3001234567',
                'email' => 'clientetest@ejemplo.com',
                'departamento_id' => $depto->id,
                'ciudad_id' => $ciudad->id,
                'direccion1' => 'Calle 10 # 5-20',
                'estado' => ClienteEstado::ACTIVO,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('clientes', ['documento' => '900123456']);
    });

    it('no permite crear cliente sin nombre', function () {
        loginComoAdmin();
        [$depto, $ciudad] = crearUbicacion();

        livewire(ClienteResource\Pages\CreateCliente::class)
            ->fillForm([
                'nombre' => '',
                'documento' => '800111222',
                'telefono' => '3009999999',
                'email' => 'sinombre@test.com',
                'departamento_id' => $depto->id,
                'ciudad_id' => $ciudad->id,
                'direccion1' => 'Cra 1',
            ])
            ->call('create')
            ->assertHasFormErrors(['nombre']);
    });

    it('no permite crear cliente con documento duplicado', function () {
        loginComoAdmin();
        [$depto, $ciudad] = crearUbicacion();

        Cliente::factory()->create(['documento' => '111222333']);

        livewire(ClienteResource\Pages\CreateCliente::class)
            ->fillForm([
                'nombre' => 'Otro Cliente',
                'documento' => '111222333',
                'telefono' => '3001111111',
                'email' => 'otro@test.com',
                'departamento_id' => $depto->id,
                'ciudad_id' => $ciudad->id,
                'direccion1' => 'Cra 2',
            ])
            ->call('create')
            ->assertHasFormErrors(['documento']);
    });
});

describe('ClienteResource — Editar', function () {

    it('el administrador puede editar un cliente', function () {
        loginComoAdmin();
        $cliente = Cliente::factory()->create(['nombre' => 'Cliente Original']);

        livewire(ClienteResource\Pages\EditCliente::class, ['record' => $cliente->id])
            ->fillForm(['nombre' => 'Cliente Editado'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('clientes', ['nombre' => 'CLIENTE EDITADO']);
    });
});

describe('ClienteResource — Ver', function () {

    it('el administrador puede ver el detalle de un cliente', function () {
        loginComoAdmin();
        $cliente = Cliente::factory()->create(['nombre' => 'Cliente Visible']);

        livewire(ClienteResource\Pages\ViewCliente::class, ['record' => $cliente->id])
            ->assertSuccessful()
            ->assertSee('CLIENTE VISIBLE');
    });
});
