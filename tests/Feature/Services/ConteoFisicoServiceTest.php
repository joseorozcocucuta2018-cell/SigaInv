<?php

use App\Enums\AjusteEstado;
use App\Enums\ConteoFisicoEstado;
use App\Enums\MotivoAjuste;
use App\Models\AjusteInventario;
use App\Models\Bodega;
use App\Models\Ciudad;
use App\Models\ConteoFisico;
use App\Models\Departamento;
use App\Models\Producto;
use App\Models\StockBodega;
use App\Services\AjusteInventarioService;
use App\Services\ConteoFisicoService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| ConteoFisicoServiceTest
|
| Verifica el flujo completo de conteo físico:
| - generarConteo: crea conteo con productos de la bodega
| - cerrarConteo: calcula diferencias
| - generarAjuste: crea AjusteInventario vinculado
| - Flujo completo: generar → registrar cantidades → cerrar → ajustar → confirmar
|--------------------------------------------------------------------------
*/

// ── Helper ──────────────────────────────────────────────────────────────────────
function setupConteo(): array
{
    $depto = Departamento::create(['nombre' => 'Depto Test']);
    $ciudad = Ciudad::create(['nombre' => 'Ciudad Test', 'departamento_id' => $depto->id]);

    $bodega = Bodega::create([
        'nombre' => 'Bodega Principal',
        'direccion1' => 'Calle 1',
        'departamento_id' => $depto->id,
        'ciudad_id' => $ciudad->id,
        'activo' => true,
    ]);

    $producto1 = Producto::create([
        'codigo' => 'COD-'.uniqid(),
        'nombre' => 'Producto A',
        'precio_venta' => 100,
        'precio_compra' => 80,
        'costo_promedio' => 80,
        'activo' => true,
    ]);

    $producto2 = Producto::create([
        'codigo' => 'COD-'.uniqid(),
        'nombre' => 'Producto B',
        'precio_venta' => 200,
        'precio_compra' => 150,
        'costo_promedio' => 150,
        'activo' => true,
    ]);

    StockBodega::create([
        'producto_id' => $producto1->id,
        'bodega_id' => $bodega->id,
        'cantidad' => 50,
    ]);

    StockBodega::create([
        'producto_id' => $producto2->id,
        'bodega_id' => $bodega->id,
        'cantidad' => 30,
    ]);

    return compact('bodega', 'producto1', 'producto2');
}

// ── generarConteo ───────────────────────────────────────────────────────────────

it('ConteoFisicoService → generarConteo crea conteo con productos de la bodega', function () {
    ['bodega' => $bodega] = setupConteo();

    $service = new ConteoFisicoService;
    $conteo = $service->generarConteo($bodega->id, 'Conteo de prueba');

    expect($conteo)->toBeInstanceOf(ConteoFisico::class);
    expect($conteo->estado)->toBe(ConteoFisicoEstado::ABIERTO);
    expect($conteo->numero)->toStartWith('CNT-');
    expect($conteo->observacion)->toBe('Conteo de prueba');
    expect($conteo->detalles)->toHaveCount(2);
});

it('ConteoFisicoService → generarConteo registra stock_sistema correcto', function () {
    ['bodega' => $bodega, 'producto1' => $p1, 'producto2' => $p2] = setupConteo();

    $conteo = (new ConteoFisicoService)->generarConteo($bodega->id);

    $detalle1 = $conteo->detalles()->where('producto_id', $p1->id)->first();
    $detalle2 = $conteo->detalles()->where('producto_id', $p2->id)->first();

    expect((float) $detalle1->stock_sistema)->toBe(50.0);
    expect((float) $detalle2->stock_sistema)->toBe(30.0);
    expect($detalle1->cantidad_contada)->toBeNull();
});

it('ConteoFisicoService → generarConteo ignora productos sin stock', function () {
    ['bodega' => $bodega] = setupConteo();

    // Producto sin stock en esta bodega
    Producto::create([
        'codigo' => 'COD-SIN-STOCK',
        'nombre' => 'Sin Stock',
        'precio_venta' => 50,
        'precio_compra' => 30,
        'activo' => true,
    ]);

    $conteo = (new ConteoFisicoService)->generarConteo($bodega->id);

    // Solo 2 productos (los que tienen stock > 0)
    expect($conteo->detalles)->toHaveCount(2);
});

// ── cerrarConteo ────────────────────────────────────────────────────────────────

it('ConteoFisicoService → cerrarConteo calcula diferencias correctamente', function () {
    ['bodega' => $bodega, 'producto1' => $p1, 'producto2' => $p2] = setupConteo();

    $service = new ConteoFisicoService;
    $conteo = $service->generarConteo($bodega->id);

    // Registrar cantidades contadas
    $conteo->detalles()->where('producto_id', $p1->id)->update(['cantidad_contada' => 55]);
    $conteo->detalles()->where('producto_id', $p2->id)->update(['cantidad_contada' => 28]);

    $service->cerrarConteo($conteo->refresh());

    $conteo->refresh();
    expect($conteo->estado)->toBe(ConteoFisicoEstado::CERRADO);
    expect($conteo->fecha_cierre)->not->toBeNull();

    $det1 = $conteo->detalles()->where('producto_id', $p1->id)->first();
    $det2 = $conteo->detalles()->where('producto_id', $p2->id)->first();

    expect((float) $det1->diferencia)->toBe(5.0);   // 55 - 50
    expect((float) $det2->diferencia)->toBe(-2.0);   // 28 - 30
});

it('ConteoFisicoService → no se puede cerrar un conteo ya cerrado', function () {
    ['bodega' => $bodega] = setupConteo();

    $service = new ConteoFisicoService;
    $conteo = $service->generarConteo($bodega->id);
    $conteo->detalles()->update(['cantidad_contada' => 50]);
    $service->cerrarConteo($conteo->refresh());

    expect(fn () => $service->cerrarConteo($conteo->refresh()))
        ->toThrow(InvalidArgumentException::class, 'abierto');
});

it('ConteoFisicoService → no se puede cerrar sin cantidades contadas', function () {
    ['bodega' => $bodega] = setupConteo();

    $service = new ConteoFisicoService;
    $conteo = $service->generarConteo($bodega->id);

    // No registrar ninguna cantidad contada
    expect(fn () => $service->cerrarConteo($conteo))
        ->toThrow(InvalidArgumentException::class, 'cantidad contada');
});

// ── generarAjuste ───────────────────────────────────────────────────────────────

it('ConteoFisicoService → generarAjuste crea AjusteInventario con diferencias', function () {
    ['bodega' => $bodega, 'producto1' => $p1, 'producto2' => $p2] = setupConteo();

    $service = new ConteoFisicoService;
    $conteo = $service->generarConteo($bodega->id);

    $conteo->detalles()->where('producto_id', $p1->id)->update(['cantidad_contada' => 55]);
    $conteo->detalles()->where('producto_id', $p2->id)->update(['cantidad_contada' => 28]);

    $service->cerrarConteo($conteo->refresh());

    $ajuste = $service->generarAjuste($conteo->refresh());

    expect($ajuste)->toBeInstanceOf(AjusteInventario::class);
    expect($ajuste->motivo)->toBe(MotivoAjuste::CONTEO_FISICO);
    expect($ajuste->estado)->toBe(AjusteEstado::BORRADOR);
    expect($ajuste->bodega_id)->toBe($bodega->id);
    expect($ajuste->detalles)->toHaveCount(2);

    // Verificar conteo queda en estado AJUSTADO
    expect($conteo->refresh()->estado)->toBe(ConteoFisicoEstado::AJUSTADO);
});

it('ConteoFisicoService → generarAjuste vincula detalles con ajuste', function () {
    ['bodega' => $bodega, 'producto1' => $p1] = setupConteo();

    $service = new ConteoFisicoService;
    $conteo = $service->generarConteo($bodega->id);

    $conteo->detalles()->where('producto_id', $p1->id)->update(['cantidad_contada' => 60]);
    // Producto2: sin contar (cantidad_contada = null) — no debe generar ajuste
    $service->cerrarConteo($conteo->refresh());

    $ajuste = $service->generarAjuste($conteo->refresh());

    // Solo 1 detalle (producto1 con diferencia)
    expect($ajuste->detalles)->toHaveCount(1);

    // El detalle del conteo tiene referencia al ajuste
    $detalleConteo = $conteo->detalles()->where('producto_id', $p1->id)->first();
    expect($detalleConteo->ajuste_inventario_id)->toBe($ajuste->id);
});

it('ConteoFisicoService → no se puede generar ajuste de conteo abierto', function () {
    ['bodega' => $bodega] = setupConteo();

    $service = new ConteoFisicoService;
    $conteo = $service->generarConteo($bodega->id);

    expect(fn () => $service->generarAjuste($conteo))
        ->toThrow(InvalidArgumentException::class, 'cerrados');
});

it('ConteoFisicoService → generarAjuste sin diferencias lanza excepcion', function () {
    ['bodega' => $bodega, 'producto1' => $p1, 'producto2' => $p2] = setupConteo();

    $service = new ConteoFisicoService;
    $conteo = $service->generarConteo($bodega->id);

    // Cantidades iguales al sistema
    $conteo->detalles()->where('producto_id', $p1->id)->update(['cantidad_contada' => 50]);
    $conteo->detalles()->where('producto_id', $p2->id)->update(['cantidad_contada' => 30]);

    $service->cerrarConteo($conteo->refresh());

    expect(fn () => $service->generarAjuste($conteo->refresh()))
        ->toThrow(InvalidArgumentException::class, 'diferencias');
});

// ── Flujo completo ──────────────────────────────────────────────────────────────

it('ConteoFisicoService → flujo completo: generar → cerrar → ajustar → confirmar ajuste', function () {
    ['bodega' => $bodega, 'producto1' => $p1, 'producto2' => $p2] = setupConteo();

    $conteoService = new ConteoFisicoService;
    $ajusteService = new AjusteInventarioService;

    // 1. Generar conteo
    $conteo = $conteoService->generarConteo($bodega->id);
    expect($conteo->detalles)->toHaveCount(2);

    // 2. Registrar cantidades (p1: +10, p2: -5)
    $conteo->detalles()->where('producto_id', $p1->id)->update(['cantidad_contada' => 60]);
    $conteo->detalles()->where('producto_id', $p2->id)->update(['cantidad_contada' => 25]);

    // 3. Cerrar conteo
    $conteoService->cerrarConteo($conteo->refresh());
    expect($conteo->refresh()->estado)->toBe(ConteoFisicoEstado::CERRADO);

    // 4. Generar ajuste
    $ajuste = $conteoService->generarAjuste($conteo->refresh());
    expect($ajuste->estado)->toBe(AjusteEstado::BORRADOR);
    expect($conteo->refresh()->estado)->toBe(ConteoFisicoEstado::AJUSTADO);

    // 5. Confirmar ajuste (aplica cambios al stock)
    $ajusteService->confirmar($ajuste);

    // 6. Verificar stock final
    $stockP1 = StockBodega::where('producto_id', $p1->id)->where('bodega_id', $bodega->id)->value('cantidad');
    $stockP2 = StockBodega::where('producto_id', $p2->id)->where('bodega_id', $bodega->id)->value('cantidad');

    expect((float) $stockP1)->toBe(60.0);  // 50 + 10
    expect((float) $stockP2)->toBe(25.0);  // 30 - 5
});

it('ConteoFisicoService → numero se genera automaticamente', function () {
    ['bodega' => $bodega] = setupConteo();

    $service = new ConteoFisicoService;
    $conteo1 = $service->generarConteo($bodega->id);
    $conteo2 = $service->generarConteo($bodega->id);

    expect($conteo1->numero)->toStartWith('CNT-');
    expect($conteo2->numero)->toStartWith('CNT-');
    expect($conteo1->numero)->not->toBe($conteo2->numero);
});
