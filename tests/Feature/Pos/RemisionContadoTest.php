<?php

declare(strict_types=1);

use App\Enums\CajaEstado;
use App\Enums\NumeracionEstado;
use App\Enums\UserEstado;
use App\Models\Bodega;
use App\Models\Caja;
use App\Models\Ciudad;
use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\FormaPago;
use App\Models\Impuesto;
use App\Models\Numeracion;
use App\Models\Producto;
use App\Models\StockBodega;
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
    $this->token = $this->user->createToken('test')->plainTextToken;

    $impuesto = Impuesto::create(['nombre' => 'IVA 19%', 'porcentaje' => 19, 'activo' => true]);
    $depto = Departamento::create(['nombre' => 'Depto']);
    $ciudad = Ciudad::create(['nombre' => 'Ciudad', 'departamento_id' => $depto->id]);
    $bodega = Bodega::create(['nombre' => 'Bodega', 'direccion1' => 'Calle', 'departamento_id' => $depto->id, 'ciudad_id' => $ciudad->id, 'activo' => true]);
    $caja = Caja::create(['nombre' => 'Caja', 'saldo_inicial' => 0, 'activo' => true, 'estado' => CajaEstado::ACTIVA, 'usuario_id' => $this->user->id]);
    $this->cliente = Cliente::create(['nombre' => 'Cliente', 'documento' => uniqid('DOC'), 'tipo_documento' => 'CC', 'telefono' => '123', 'email' => uniqid().'@t.com', 'direccion1' => '-', 'departamento_id' => $depto->id, 'ciudad_id' => $ciudad->id]);
    $this->producto = Producto::create(['codigo' => uniqid('P'), 'nombre' => 'Producto', 'precio_venta' => 5000, 'precio_compra' => 2500, 'impuesto_id' => $impuesto->id, 'activo' => true]);
    StockBodega::create(['producto_id' => $this->producto->id, 'bodega_id' => $bodega->id, 'cantidad' => 30]);
    Numeracion::create(['tipo_documento' => 'remision', 'resolucion_numero' => 'REM-001', 'prefijo' => 'REM', 'consecutivo_desde' => 1, 'consecutivo_hasta' => 9999, 'consecutivo_actual' => 0, 'anno' => now()->year, 'estado' => NumeracionEstado::ACTIVO]);
    $this->formaPago = FormaPago::create(['nombre' => 'Efectivo', 'activo' => true, 'requiere_banco' => false]);

    TurnoService::abrir($this->user, ['caja_id' => $caja->id, 'bodega_id' => $bodega->id, 'saldo_inicial' => 0]);
});

it('crea remisión contado sin IVA vía API POS', function () {
    $response = $this->withToken($this->token)->postJson('/pos/api/remisiones', [
        'cliente_id' => $this->cliente->id,
        'items' => [['producto_id' => $this->producto->id, 'cantidad' => 2]],
        'pagos' => [['forma_pago_id' => $this->formaPago->id, 'monto' => 10000]],
    ]);

    $response->assertStatus(201);
    $response->assertJsonStructure(['id', 'numero', 'total', 'data']);
    $this->assertEquals(10000.0, $response->json('total'));

    $this->assertDatabaseHas('remisiones', [
        'id' => $response->json('id'),
        'impuestos' => 0,
    ]);
});

it('remisión descuenta stock correctamente', function () {
    $this->withToken($this->token)->postJson('/pos/api/remisiones', [
        'cliente_id' => $this->cliente->id,
        'items' => [['producto_id' => $this->producto->id, 'cantidad' => 3]],
        'pagos' => [['forma_pago_id' => $this->formaPago->id, 'monto' => 15000]],
    ]);

    $this->assertDatabaseHas('movimientos_inventario', [
        'documento_tipo' => 'remision',
        'producto_id' => $this->producto->id,
        'cantidad' => 3,
    ]);
});
