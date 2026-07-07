<?php

use App\Enums\TransformacionEstado;
use App\Enums\TransformacionTipo;
use App\Models\Bodega;
use App\Models\Ciudad;
use App\Models\Departamento;
use App\Models\FormulaTransformacion;
use App\Models\FormulaTransformacionDetalle;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\StockBodega;
use App\Models\Transformacion;
use App\Models\TransformacionDetalle;
use App\Services\TransformacionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| TransformacionServiceTest
|
| Verifica el comportamiento del servicio de transformación de productos:
|
| validateInsumsAvailability:
|   - No lanza excepción con stock suficiente
|   - Lanza excepción con stock insuficiente
|   - El mensaje menciona el producto en falta
|
| applyFormulaToTransformacion:
|   - Crea detalles de insumos en la transformación
|   - Multiplica cantidades por cantidadAProducir
|   - Actualiza formula_transformacion_id y cantidad_a_producir
|   - Elimina detalles previos antes de aplicar
|
| confirmar:
|   - Descuenta insumos y suma producto final al stock
|   - Crea fórmula automáticamente si no existía
|   - Actualiza estado a confirmada
|   - Lanza excepción con stock insuficiente
|
| revertir:
|   - Devuelve insumos al stock y descuenta el producto final
|   - Actualiza estado a revertida
|--------------------------------------------------------------------------
*/

// ── Helper: setup base ────────────────────────────────────────────────────────

function setupTransformacion(): array
{
    loginComoAdmin();
    $refs = crearReferenciasProducto();

    $depto = Departamento::create(['nombre' => 'Depto Test']);
    $ciudad = Ciudad::create(['nombre' => 'Ciudad Test', 'departamento_id' => $depto->id]);

    $bodega = Bodega::create([
        'nombre' => 'Bodega Test',
        'direccion1' => 'Calle 1',
        'departamento_id' => $depto->id,
        'ciudad_id' => $ciudad->id,
    ]);

    $insumo1 = Producto::create([
        'codigo' => 'INS-'.uniqid(),
        'nombre' => 'Insumo A',
        'precio_compra' => 50,
        'activo' => true,
        'usuario_id' => Auth::id(),
        'marca_id' => $refs->marca->id,
        'categoria_id' => $refs->categoria->id,
        'impuesto_id' => $refs->impuesto->id,
        'unidad_medida_id' => $refs->unidad->id,
        'costo_promedio' => 0,
    ]);

    $insumo2 = Producto::create([
        'codigo' => 'INS-'.uniqid(),
        'nombre' => 'Insumo B',
        'precio_compra' => 30,
        'activo' => true,
        'usuario_id' => Auth::id(),
        'marca_id' => $refs->marca->id,
        'categoria_id' => $refs->categoria->id,
        'impuesto_id' => $refs->impuesto->id,
        'unidad_medida_id' => $refs->unidad->id,
        'costo_promedio' => 0,
    ]);

    $productoFinal = Producto::create([
        'codigo' => 'PRD-'.uniqid(),
        'nombre' => 'Producto Final',
        'precio_venta' => 200,
        'tipo_producto' => 'manufacturado',
        'activo' => true,
        'usuario_id' => Auth::id(),
        'marca_id' => $refs->marca->id,
        'categoria_id' => $refs->categoria->id,
        'impuesto_id' => $refs->impuesto->id,
        'unidad_medida_id' => $refs->unidad->id,
        'costo_promedio' => 0,
    ]);

    StockBodega::create(['producto_id' => $insumo1->id, 'bodega_id' => $bodega->id, 'cantidad' => 100]);
    StockBodega::create(['producto_id' => $insumo2->id, 'bodega_id' => $bodega->id, 'cantidad' => 50]);

    // Fórmula: 2 insumo1 + 1 insumo2 → 1 producto final
    $formula = FormulaTransformacion::create([
        'producto_final_nombre' => 'Fórmula Test',
        'tipo' => 'fabricacion',
        'producto_final_id' => $productoFinal->id,
        'activo' => true,
    ]);

    FormulaTransformacionDetalle::create([
        'formula_transformacion_id' => $formula->id,
        'producto_id' => $insumo1->id,
        'tipo_linea' => 'insumo',
        'cantidad' => 2,
    ]);

    FormulaTransformacionDetalle::create([
        'formula_transformacion_id' => $formula->id,
        'producto_id' => $insumo2->id,
        'tipo_linea' => 'insumo',
        'cantidad' => 1,
    ]);

    $transformacion = Transformacion::create([
        'bodega_id' => $bodega->id,
        'producto_final_id' => $productoFinal->id,
        'estado' => TransformacionEstado::BORRADOR,
        'fecha' => now(),
    ]);

    return compact('bodega', 'insumo1', 'insumo2', 'productoFinal', 'formula', 'transformacion');
}

/** Crea una transformación lista para confirmar (con insumos en detalles y stock). */
function setupTransformacionConInsumos(bool $conFormula = true): array
{
    $data = setupTransformacion();

    /** @var Transformacion $transformacion */
    $transformacion = $data['transformacion'];

    if ($conFormula) {
        $transformacion->update([
            'formula_transformacion_id' => $data['formula']->id,
            'cantidad_a_producir' => 2,
        ]);
    } else {
        $transformacion->update(['cantidad_a_producir' => 2]);
    }

    // Insumos: 2 × insumo1 × 2 producir = 4 / 1 × insumo2 × 2 = 2
    TransformacionDetalle::create([
        'transformacion_id' => $transformacion->id,
        'tipo_linea' => 'insumo',
        'producto_id' => $data['insumo1']->id,
        'cantidad' => 4,
        'costo_unitario' => 50,
    ]);

    TransformacionDetalle::create([
        'transformacion_id' => $transformacion->id,
        'tipo_linea' => 'insumo',
        'producto_id' => $data['insumo2']->id,
        'cantidad' => 2,
        'costo_unitario' => 30,
    ]);

    return $data;
}

// ── validateInsumsAvailability ────────────────────────────────────────────────

it('validateInsumsAvailability no lanza con stock suficiente', function () {
    ['formula' => $formula, 'bodega' => $bodega] = setupTransformacion();

    expect(fn () => app(TransformacionService::class)->validateInsumsAvailability($formula, $bodega->id, 5))
        ->not->toThrow(Exception::class);
});

it('validateInsumsAvailability lanza con stock insuficiente', function () {
    ['formula' => $formula, 'bodega' => $bodega] = setupTransformacion();

    expect(fn () => app(TransformacionService::class)->validateInsumsAvailability($formula, $bodega->id, 60))
        ->toThrow(Exception::class, 'Stock insuficiente');
});

it('validateInsumsAvailability menciona el producto en el error', function () {
    ['formula' => $formula, 'bodega' => $bodega] = setupTransformacion();

    try {
        app(TransformacionService::class)->validateInsumsAvailability($formula, $bodega->id, 60);
        fail('Se esperaba excepción');
    } catch (Exception $e) {
        expect($e->getMessage())->toContain('Stock insuficiente');
    }
});

// ── applyFormulaToTransformacion ─────────────────────────────────────────────

it('applyFormulaToTransformacion crea detalles de insumos en la transformacion', function () {
    ['formula' => $formula, 'transformacion' => $transformacion] = setupTransformacion();

    app(TransformacionService::class)->applyFormulaToTransformacion($transformacion, $formula, 1);

    // Solo insumos (2 líneas), el producto final ya es producto_final_id
    expect($transformacion->detalles()->count())->toBe(2);
});

it('applyFormulaToTransformacion multiplica cantidades por cantidadAProducir', function () {
    ['formula' => $formula, 'transformacion' => $transformacion, 'insumo1' => $insumo1] = setupTransformacion();

    app(TransformacionService::class)->applyFormulaToTransformacion($transformacion, $formula, 5);

    $detalle = $transformacion->detalles()->where('producto_id', $insumo1->id)->first();

    // insumo1: cantidad_formula=2 × cantidadAProducir=5 = 10
    expect((float) $detalle->cantidad)->toBe(10.0);
});

it('applyFormulaToTransformacion actualiza formula_transformacion_id', function () {
    ['formula' => $formula, 'transformacion' => $transformacion] = setupTransformacion();

    app(TransformacionService::class)->applyFormulaToTransformacion($transformacion, $formula, 3);

    expect($transformacion->refresh()->formula_transformacion_id)->toBe($formula->id);
    expect((float) $transformacion->refresh()->cantidad_a_producir)->toBe(3.0);
});

it('applyFormulaToTransformacion elimina detalles previos antes de aplicar', function () {
    ['formula' => $formula, 'transformacion' => $transformacion] = setupTransformacion();

    app(TransformacionService::class)->applyFormulaToTransformacion($transformacion, $formula, 1);
    app(TransformacionService::class)->applyFormulaToTransformacion($transformacion, $formula, 2);

    // Segunda aplicación reemplaza la primera — sigue siendo 2 líneas (no 4)
    expect($transformacion->detalles()->count())->toBe(2);
});

// ── confirmar ────────────────────────────────────────────────────────────────

it('confirmar descuenta insumos y suma producto final al stock', function () {
    $data = setupTransformacionConInsumos(conFormula: true);

    app(TransformacionService::class)->confirmar($data['transformacion']);

    // Insumo1: 100 - 4 = 96
    expect((float) StockBodega::where('producto_id', $data['insumo1']->id)->value('cantidad'))
        ->toBe(96.0);

    // Insumo2: 50 - 2 = 48
    expect((float) StockBodega::where('producto_id', $data['insumo2']->id)->value('cantidad'))
        ->toBe(48.0);

    // ProductoFinal: 0 + 2 = 2
    expect((float) StockBodega::where('producto_id', $data['productoFinal']->id)->value('cantidad'))
        ->toBe(2.0);
});

it('confirmar actualiza el estado a confirmada', function () {
    $data = setupTransformacionConInsumos(conFormula: true);

    app(TransformacionService::class)->confirmar($data['transformacion']);

    $transformacion = $data['transformacion']->refresh();
    expect($transformacion->estado)->toBe(TransformacionEstado::CONFIRMADA);
    expect($transformacion->confirmada_en)->not->toBeNull();
});

it('confirmar guarda el costo total en la transformacion', function () {
    $data = setupTransformacionConInsumos(conFormula: true);

    app(TransformacionService::class)->confirmar($data['transformacion']);

    $transformacion = $data['transformacion']->refresh();
    // 4 insumo1 × 50 + 2 insumo2 × 30 = 200 + 60 = 260
    expect((float) $transformacion->costo_total)->toBe(260.0);
});

it('confirmar crea movimientos de inventario', function () {
    $data = setupTransformacionConInsumos(conFormula: true);

    app(TransformacionService::class)->confirmar($data['transformacion']);

    // 2 salidas (insumos) + 1 entrada (producto final)
    expect(MovimientoInventario::where('documento_tipo', 'transformacion')
        ->where('documento_id', $data['transformacion']->id)
        ->count()
    )->toBe(3);
});

it('confirmar sin formula crea FormulaTransformacion automaticamente', function () {
    $data = setupTransformacionConInsumos(conFormula: false);

    expect(FormulaTransformacion::count())->toBe(1); // solo la del setup

    app(TransformacionService::class)->confirmar($data['transformacion']);

    // Debe haberse creado una nueva fórmula Auto:
    expect(FormulaTransformacion::count())->toBe(2);

    $formulaAuto = FormulaTransformacion::orderByDesc('id')->first();
    expect($formulaAuto->producto_final_nombre)->toStartWith('AUTO:');
    expect($formulaAuto->producto_final_id)->toBe($data['productoFinal']->id);
    expect($formulaAuto->detalles()->count())->toBe(2); // 2 insumos normalizados

    // La transformación queda vinculada a la fórmula creada
    expect($data['transformacion']->refresh()->formula_transformacion_id)->toBe($formulaAuto->id);
});

it('confirmar lanza excepcion con stock insuficiente', function () {
    $data = setupTransformacion();

    // Insumos con cantidad mayor al stock disponible
    TransformacionDetalle::create([
        'transformacion_id' => $data['transformacion']->id,
        'tipo_linea' => 'insumo',
        'producto_id' => $data['insumo2']->id,
        'cantidad' => 999, // solo hay 50
        'costo_unitario' => 30,
    ]);
    $data['transformacion']->update(['cantidad_a_producir' => 1]);

    expect(fn () => app(TransformacionService::class)->confirmar($data['transformacion']))
        ->toThrow(Exception::class, 'Stock insuficiente');
});

// ── revertir ─────────────────────────────────────────────────────────────────

it('revertir devuelve insumos al stock y descuenta producto final', function () {
    $data = setupTransformacionConInsumos(conFormula: true);

    // Tipo combo para que sea reversible
    $data['transformacion']->update(['tipo' => TransformacionTipo::COMBO]);

    app(TransformacionService::class)->confirmar($data['transformacion']);

    // Verificar estado antes de revertir
    expect($data['transformacion']->refresh()->estado)->toBe(TransformacionEstado::CONFIRMADA);

    app(TransformacionService::class)->revertir($data['transformacion']->refresh());

    // Stock restaurado a valores originales
    expect((float) StockBodega::where('producto_id', $data['insumo1']->id)->value('cantidad'))
        ->toBe(100.0);
    expect((float) StockBodega::where('producto_id', $data['insumo2']->id)->value('cantidad'))
        ->toBe(50.0);
    expect((float) StockBodega::where('producto_id', $data['productoFinal']->id)->value('cantidad'))
        ->toBe(0.0);
});

it('revertir actualiza estado a revertida', function () {
    $data = setupTransformacionConInsumos(conFormula: true);
    $data['transformacion']->update(['tipo' => TransformacionTipo::COMBO]);

    app(TransformacionService::class)->confirmar($data['transformacion']);
    app(TransformacionService::class)->revertir($data['transformacion']->refresh());

    expect($data['transformacion']->refresh()->estado)->toBe(TransformacionEstado::REVERTIDA);
    expect($data['transformacion']->refresh()->revertida_en)->not->toBeNull();
});
