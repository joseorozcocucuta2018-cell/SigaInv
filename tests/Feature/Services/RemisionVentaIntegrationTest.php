<?php

namespace Tests\Feature\Services;

use App\Enums\RemisionEstado;
use App\Enums\VentaEstado;
use App\Models\Bodega;
use App\Models\Ciudad;
use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\DetalleRemision;
use App\Models\DetalleVenta;
use App\Models\MovimientoInventario;
use App\Models\Numeracion;
use App\Models\Producto;
use App\Models\Remision;
use App\Models\StockBodega;
use App\Models\Venta;
use App\Services\RemisionService;
use App\Services\VentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemisionVentaIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Bodega $bodega;

    protected Producto $producto;

    protected Cliente $cliente;

    protected int $initialStock = 100;

    protected function setUp(): void
    {
        parent::setUp();

        Numeracion::create([
            'tipo_documento' => 'venta',
            'prefijo' => 'VEN',
            'consecutivo_desde' => 1,
            'consecutivo_hasta' => 9999,
            'consecutivo_actual' => 0,
            'anno' => now()->year,
            'resolucion_numero' => 'TEST-00001',
        ]);

        $depto = Departamento::create(['nombre' => 'Depto Test']);
        $ciudad = Ciudad::create(['nombre' => 'Ciudad Test', 'departamento_id' => $depto->id]);

        $this->bodega = Bodega::create([
            'nombre' => 'Bodega Test',
            'direccion1' => 'Calle 1',
            'departamento_id' => $depto->id,
            'ciudad_id' => $ciudad->id,
            'activo' => true,
        ]);

        $this->cliente = Cliente::create([
            'nombre' => 'Cliente Test',
            'documento' => 'DOC'.uniqid(),
            'tipo_documento' => 'CC',
            'telefono' => '123',
            'email' => 'test-'.uniqid().'@test.com',
            'direccion1' => 'Calle 1',
            'departamento_id' => $depto->id,
            'ciudad_id' => $ciudad->id,
        ]);

        $this->producto = Producto::create([
            'codigo' => 'COD-'.uniqid(),
            'nombre' => 'Producto Test',
            'precio_venta' => 100,
            'activo' => true,
        ]);

        StockBodega::create([
            'producto_id' => $this->producto->id,
            'bodega_id' => $this->bodega->id,
            'cantidad' => $this->initialStock,
        ]);
    }

    private function crearVenta(array $extra = []): Venta
    {
        return Venta::create(array_merge([
            'numero' => 'VEN-'.uniqid(),
            'estado' => VentaEstado::BORRADOR,
            'cliente_id' => $this->cliente->id,
            'bodega_id' => $this->bodega->id,
            'remision_id' => null,
            'subtotal' => 0,
            'descuento' => 0,
            'impuestos' => 0,
            'total' => 0,
            'saldo_pendiente' => 0,
            'estado_pago' => 'pendiente',
        ], $extra));
    }

    private function crearRemision(array $extra = []): Remision
    {
        return Remision::create(array_merge([
            'numero' => 'REM-'.uniqid(),
            'estado' => RemisionEstado::BORRADOR,
            'cliente_id' => $this->cliente->id,
            'bodega_id' => $this->bodega->id,
            'subtotal' => 0,
            'descuento' => 0,
            'impuestos' => 0,
            'total' => 0,
            'saldo_pendiente' => 0,
            'estado_pago' => 'pendiente',
        ], $extra));
    }

    public function test_venta_directa_descuenta_stock(): void
    {
        $venta = $this->crearVenta();

        $detalles = 5;
        for ($i = 0; $i < $detalles; $i++) {
            DetalleVenta::create([
                'venta_id' => $venta->id,
                'producto_id' => $this->producto->id,
                'cantidad' => 10,
                'precio_unitario' => 100,
            ]);
        }

        VentaService::confirmar($venta);

        $totalDescuento = 10 * $detalles;
        $stockActual = StockBodega::where('producto_id', $this->producto->id)
            ->where('bodega_id', $this->bodega->id)
            ->value('cantidad');

        $this->assertEquals($this->initialStock - $totalDescuento, $stockActual);
        $this->assertEquals(VentaEstado::CONFIRMADA, $venta->refresh()->estado);

        $movimientos = MovimientoInventario::where('documento_tipo', 'venta')
            ->where('documento_id', $venta->id)
            ->get();

        $this->assertEquals($detalles, $movimientos->count());
        $this->assertTrue($movimientos->every(fn ($m) => $m->tipo_movimiento->value === 'salida_venta'));
    }

    public function test_venta_con_remision_no_descuenta_dos_veces(): void
    {
        $remision = $this->crearRemision();

        $cantidadRemision = 30;
        DetalleRemision::create([
            'remision_id' => $remision->id,
            'producto_id' => $this->producto->id,
            'cantidad' => $cantidadRemision,
            'precio_unitario' => 100,
        ]);

        RemisionService::confirmar($remision);

        $stockDespuesRemision = StockBodega::where('producto_id', $this->producto->id)
            ->where('bodega_id', $this->bodega->id)
            ->value('cantidad');

        $venta = $this->crearVenta(['remision_id' => $remision->id]);

        DetalleVenta::create([
            'venta_id' => $venta->id,
            'producto_id' => $this->producto->id,
            'cantidad' => $cantidadRemision,
            'precio_unitario' => 100,
        ]);

        VentaService::confirmar($venta);

        $stockDespuesVenta = StockBodega::where('producto_id', $this->producto->id)
            ->where('bodega_id', $this->bodega->id)
            ->value('cantidad');

        $this->assertEquals($stockDespuesRemision, $stockDespuesVenta);
        $this->assertEquals($this->initialStock - $cantidadRemision, $stockDespuesVenta);
        $this->assertEquals(VentaEstado::CONFIRMADA, $venta->refresh()->estado);
        $this->assertEquals(RemisionEstado::FACTURADA, $remision->refresh()->estado);

        $movimientoVenta = MovimientoInventario::where('documento_tipo', 'venta')
            ->where('documento_id', $venta->id)
            ->first();

        $this->assertNotNull($movimientoVenta);
        $this->assertEquals('facturacion_remision', $movimientoVenta->tipo_movimiento->value);
    }

    public function test_anular_venta_directa_recupera_stock(): void
    {
        $venta = $this->crearVenta();

        DetalleVenta::create([
            'venta_id' => $venta->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 20,
            'precio_unitario' => 100,
        ]);

        VentaService::confirmar($venta);
        VentaService::anular($venta);

        $stockDespues = StockBodega::where('producto_id', $this->producto->id)
            ->where('bodega_id', $this->bodega->id)
            ->value('cantidad');

        $this->assertEquals($this->initialStock, $stockDespues);
        $this->assertEquals(VentaEstado::ANULADA, $venta->refresh()->estado);

        $movimientos = MovimientoInventario::where('documento_tipo', 'venta')
            ->where('documento_id', $venta->id)
            ->get();

        $this->assertEquals(2, $movimientos->count());
        $this->assertTrue($movimientos->contains(fn ($m) => $m->tipo_movimiento->value === 'reverso_anulacion'));
    }

    public function test_anular_venta_con_remision_no_recupera_stock(): void
    {
        $remision = $this->crearRemision();

        DetalleRemision::create([
            'remision_id' => $remision->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 25,
            'precio_unitario' => 100,
        ]);

        RemisionService::confirmar($remision);

        $venta = $this->crearVenta(['remision_id' => $remision->id]);

        DetalleVenta::create([
            'venta_id' => $venta->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 25,
            'precio_unitario' => 100,
        ]);

        VentaService::confirmar($venta);

        $stockAntesDe = StockBodega::where('producto_id', $this->producto->id)
            ->where('bodega_id', $this->bodega->id)
            ->value('cantidad');

        VentaService::anular($venta);

        $stockDespues = StockBodega::where('producto_id', $this->producto->id)
            ->where('bodega_id', $this->bodega->id)
            ->value('cantidad');

        $this->assertEquals($stockAntesDe, $stockDespues);
        $this->assertEquals(RemisionEstado::CONFIRMADA, $remision->refresh()->estado);
        $this->assertEquals(VentaEstado::ANULADA, $venta->refresh()->estado);

        $movimiento = MovimientoInventario::where('documento_tipo', 'venta')
            ->where('documento_id', $venta->id)
            ->where('tipo_movimiento', 'anulacion_venta_remision')
            ->first();

        $this->assertNotNull($movimiento);
    }

    public function test_no_permite_anular_remision_con_venta_confirmada(): void
    {
        $remision = $this->crearRemision();

        DetalleRemision::create([
            'remision_id' => $remision->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 10,
            'precio_unitario' => 100,
        ]);

        RemisionService::confirmar($remision);

        $venta = $this->crearVenta(['remision_id' => $remision->id]);

        DetalleVenta::create([
            'venta_id' => $venta->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 10,
            'precio_unitario' => 100,
        ]);

        VentaService::confirmar($venta);

        // Tras confirmar la venta, la remisión queda FACTURADA.
        // Intentar anularla debe lanzar excepción (transición no permitida desde FACTURADA).
        $this->expectException(\InvalidArgumentException::class);

        RemisionService::anular($remision);
    }

    public function test_permite_anular_remision_si_venta_fue_anulada(): void
    {
        $remision = $this->crearRemision();

        DetalleRemision::create([
            'remision_id' => $remision->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 10,
            'precio_unitario' => 100,
        ]);

        RemisionService::confirmar($remision);

        $venta = $this->crearVenta(['remision_id' => $remision->id]);

        DetalleVenta::create([
            'venta_id' => $venta->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 10,
            'precio_unitario' => 100,
        ]);

        VentaService::confirmar($venta);
        VentaService::anular($venta);
        RemisionService::anular($remision);

        $this->assertEquals(RemisionEstado::ANULADA, $remision->refresh()->estado);
    }
}
