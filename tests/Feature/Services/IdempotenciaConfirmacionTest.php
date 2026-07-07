<?php

namespace Tests\Feature\Services;

use App\Enums\VentaEstado;
use App\Models\Bodega;
use App\Models\Ciudad;
use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\DetalleVenta;
use App\Models\MovimientoInventario;
use App\Models\Numeracion;
use App\Models\Producto;
use App\Models\StockBodega;
use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Req. 15 — Idempotencia de confirmación
 *
 * Verifica que confirmar dos veces el mismo documento no duplique movimientos:
 * - La máquina de estados impide segunda confirmación (InvalidArgumentException).
 * - En caso de bypass, el índice único en movimientos_inventario evita duplicados.
 */
class IdempotenciaConfirmacionTest extends TestCase
{
    use RefreshDatabase;

    protected Bodega $bodega;

    protected Producto $producto;

    protected Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();

        Numeracion::create([
            'tipo_documento' => 'venta',
            'resolucion_numero' => 'TEST-00001',
            'prefijo' => 'VEN',
            'consecutivo_desde' => 1,
            'consecutivo_hasta' => 9999,
            'consecutivo_actual' => 0,
            'anno' => now()->year,
        ]);

        $depto = Departamento::create(['nombre' => 'Test Dept']);
        $ciudad = Ciudad::create(['nombre' => 'Test City', 'departamento_id' => $depto->id]);

        $this->bodega = Bodega::create([
            'nombre' => 'Bodega Test '.uniqid(),
            'direccion1' => 'Calle 1',
            'departamento_id' => $depto->id,
            'ciudad_id' => $ciudad->id,
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
        ]);

        StockBodega::create([
            'producto_id' => $this->producto->id,
            'bodega_id' => $this->bodega->id,
            'cantidad' => 100,
        ]);
    }

    /**
     * Confirmar dos veces el mismo documento lanza excepción y no duplica movimientos.
     */
    public function test_confirmar_dos_veces_lanza_excepcion_y_no_duplica_movimientos(): void
    {
        $venta = Venta::create([
            'numero' => 'VEN-'.uniqid(),
            'estado' => VentaEstado::BORRADOR,
            'cliente_id' => $this->cliente->id,
            'bodega_id' => $this->bodega->id,
            'remision_id' => null,
            'subtotal' => 100,
            'descuento' => 0,
            'impuestos' => 0,
            'total' => 100,
            'saldo_pendiente' => 100,
            'estado_pago' => 'pendiente',
        ]);

        DetalleVenta::create([
            'venta_id' => $venta->id,
            'producto_id' => $this->producto->id,
            'cantidad' => 10,
            'precio_unitario' => 10,
        ]);

        // Primera confirmación
        VentaService::confirmar($venta->refresh());

        $movimientosDespuesPrimera = MovimientoInventario::where('documento_tipo', 'venta')
            ->where('documento_id', $venta->id)
            ->count();
        $this->assertSame(1, $movimientosDespuesPrimera);

        // Segunda confirmación debe lanzar InvalidArgumentException y no crear más movimientos
        try {
            VentaService::confirmar($venta->refresh());
            $this->fail('Se esperaba InvalidArgumentException al confirmar por segunda vez.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('Transición no permitida', $e->getMessage());
        }

        $movimientosDespuesSegunda = MovimientoInventario::where('documento_tipo', 'venta')
            ->where('documento_id', $venta->id)
            ->count();
        $this->assertSame(1, $movimientosDespuesSegunda, 'No debe haber movimientos duplicados tras intento de segunda confirmación.');
    }
}
