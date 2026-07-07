<?php

/*
|--------------------------------------------------------------------------
| FormulaTransformacionResourceTest.php — Tests del recurso FormulaTransformacion
|
| Cubre:
|   - Listado: renderiza, muestra fórmulas existentes
|   - Crear: page renderiza, crea con datos válidos, valida campos obligatorios
|   - Editar: page renderiza, actualiza, desactiva
|   - Vinculación: fórmula queda vinculada al producto_final_id correcto
|--------------------------------------------------------------------------
*/

use App\Filament\Resources\FormulaTransformacions\Pages\CreateFormulaTransformacion;
use App\Filament\Resources\FormulaTransformacions\Pages\EditFormulaTransformacion;
use App\Filament\Resources\FormulaTransformacions\Pages\ListFormulaTransformacions;
use App\Models\FormulaTransformacion;
use App\Models\FormulaTransformacionDetalle;
use App\Models\Producto;

use Illuminate\Support\Facades\Auth;

use function Pest\Livewire\livewire;

// ── Helper local ──────────────────────────────────────────────────────────────

function crearProductoParaFormula(string $tipo = 'manufacturado'): Producto
{
    $refs = crearReferenciasProducto();

    return Producto::create([
        'codigo' => 'PRD-FML-'.uniqid(),
        'nombre' => 'Producto Fórmula '.uniqid(),
        'precio_venta' => 500,
        'tipo_producto' => $tipo,
        'activo' => true,
        'usuario_id' => Auth::id(),
        'marca_id' => $refs->marca->id,
        'categoria_id' => $refs->categoria->id,
        'impuesto_id' => $refs->impuesto->id,
        'unidad_medida_id' => $refs->unidad->id,
        'costo_promedio' => 0,
    ]);
}

function crearInsumoParaFormula(): Producto
{
    $refs = crearReferenciasProducto();

    return Producto::create([
        'codigo' => 'INS-FML-'.uniqid(),
        'nombre' => 'Insumo Fórmula '.uniqid(),
        'precio_compra' => 80,
        'activo' => true,
        'usuario_id' => Auth::id(),
        'marca_id' => $refs->marca->id,
        'categoria_id' => $refs->categoria->id,
        'impuesto_id' => $refs->impuesto->id,
        'unidad_medida_id' => $refs->unidad->id,
        'costo_promedio' => 0,
    ]);
}

function crearFormulaCompleta(): FormulaTransformacion
{
    $productoFinal = crearProductoParaFormula();
    $insumo = crearInsumoParaFormula();

    $formula = FormulaTransformacion::create([
        'producto_final_nombre' => 'Fórmula Completa '.uniqid(),
        'tipo' => 'fabricacion',
        'producto_final_id' => $productoFinal->id,
        'cantidad_producto_final' => 1,
        'activo' => true,
    ]);

    FormulaTransformacionDetalle::create([
        'formula_transformacion_id' => $formula->id,
        'producto_id' => $insumo->id,
        'tipo_linea' => 'insumo',
        'cantidad' => 2,
    ]);

    return $formula;
}

// ── Listado ───────────────────────────────────────────────────────────────────

describe('FormulaTransformacionResource — Listado', function () {

    it('el administrador puede ver la lista de fórmulas', function () {
        loginComoAdmin();

        livewire(ListFormulaTransformacions::class)
            ->assertSuccessful();
    });

    it('muestra las fórmulas existentes en la tabla', function () {
        loginComoAdmin();
        $formula = crearFormulaCompleta();

        livewire(ListFormulaTransformacions::class)
            ->assertSuccessful()
            ->assertSee($formula->producto_final_nombre);
    });

    it('muestra el tipo de fórmula en la tabla', function () {
        loginComoAdmin();
        crearFormulaCompleta();

        livewire(ListFormulaTransformacions::class)
            ->assertSuccessful()
            ->assertSee('Fabricación');
    });

    it('lista vacía renderiza sin errores', function () {
        loginComoAdmin();

        livewire(ListFormulaTransformacions::class)
            ->assertSuccessful();

        $this->assertDatabaseCount('formula_transformaciones', 0);
    });
});

// ── Crear ─────────────────────────────────────────────────────────────────────

describe('FormulaTransformacionResource — Crear', function () {

    it('la página de creación renderiza sin errores', function () {
        loginComoAdmin();

        livewire(CreateFormulaTransformacion::class)
            ->assertSuccessful();
    });

    it('puede crear una fórmula con los datos obligatorios', function () {
        loginComoAdmin();
        $insumo = crearInsumoParaFormula();

        livewire(CreateFormulaTransformacion::class)
            ->fillForm([
                'producto_final_nombre' => 'Fórmula Nueva Test',
                'tipo' => 'fabricacion',
                'cantidad_producto_final' => 1,
                'detalles' => [
                    ['producto_id' => $insumo->id, 'cantidad' => 2],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('formula_transformaciones', [
            'producto_final_nombre' => 'FÓRMULA NUEVA TEST',
        ]);
        $this->assertDatabaseHas('productos', [
            'nombre' => 'FÓRMULA NUEVA TEST',
            'tipo_producto' => 'manufacturado',
        ]);
    });

    it('no permite producto_final_nombre duplicado', function () {
        loginComoAdmin();
        $productoFinal1 = crearProductoParaFormula();

        FormulaTransformacion::create([
            'producto_final_nombre' => 'NOMBRE ÚNICO TEST',
            'tipo' => 'fabricacion',
            'producto_final_id' => $productoFinal1->id,
            'activo' => true,
        ]);

        livewire(CreateFormulaTransformacion::class)
            ->fillForm([
                'producto_final_nombre' => 'NOMBRE ÚNICO TEST',
                'tipo' => 'fabricacion',
                'cantidad_producto_final' => 1,
            ])
            ->call('create')
            ->assertHasFormErrors(['producto_final_nombre']);
    });

    it('crea fórmula tipo combo correctamente', function () {
        loginComoAdmin();
        $insumo = crearInsumoParaFormula();

        livewire(CreateFormulaTransformacion::class)
            ->fillForm([
                'producto_final_nombre' => 'Combo Test',
                'tipo' => 'combo',
                'cantidad_producto_final' => 1,
                'detalles' => [
                    ['producto_id' => $insumo->id, 'cantidad' => 1],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('formula_transformaciones', [
            'producto_final_nombre' => 'COMBO TEST',
            'tipo' => 'combo',
        ]);
    });
});

// ── Editar ────────────────────────────────────────────────────────────────────

describe('FormulaTransformacionResource — Editar', function () {

    it('la página de edición renderiza sin errores', function () {
        loginComoAdmin();
        $formula = crearFormulaCompleta();

        livewire(EditFormulaTransformacion::class, ['record' => $formula->id])
            ->assertSuccessful();
    });

    it('puede desactivar una fórmula', function () {
        loginComoAdmin();
        $formula = crearFormulaCompleta();

        livewire(EditFormulaTransformacion::class, ['record' => $formula->id])
            ->fillForm(['activo' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('formula_transformaciones', [
            'id' => $formula->id,
            'activo' => false,
        ]);
    });
});

// ── Vinculación con Transformaciones ─────────────────────────────────────────

describe('FormulaTransformacionResource — Vinculación', function () {

    it('la fórmula queda vinculada al producto_final_id correcto', function () {
        loginComoAdmin();
        $insumo = crearInsumoParaFormula();

        livewire(CreateFormulaTransformacion::class)
            ->fillForm([
                'producto_final_nombre' => 'Fórmula Vinculada',
                'tipo' => 'fabricacion',
                'cantidad_producto_final' => 1,
                'detalles' => [
                    ['producto_id' => $insumo->id, 'cantidad' => 1],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $formula = FormulaTransformacion::where('producto_final_nombre', 'FÓRMULA VINCULADA')->first();
        $productoFinal = Producto::where('nombre', 'FÓRMULA VINCULADA')->first();

        expect($formula)->not->toBeNull();
        expect($productoFinal)->not->toBeNull();
        expect($formula->producto_final_id)->toBe($productoFinal->id);
        expect($formula->productoFinal)->not->toBeNull();
        expect($formula->productoFinal->nombre)->toBe('FÓRMULA VINCULADA');
    });

    it('crea el producto final automáticamente desde el nombre en el formulario', function () {
        loginComoAdmin();
        $insumo = crearInsumoParaFormula();

        livewire(CreateFormulaTransformacion::class)
            ->fillForm([
                'producto_final_nombre' => 'Producto Creado Automáticamente',
                'tipo' => 'fabricacion',
                'cantidad_producto_final' => 1,
                'detalles' => [
                    ['producto_id' => $insumo->id, 'cantidad' => 1],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('productos', [
            'nombre' => 'PRODUCTO CREADO AUTOMÁTICAMENTE',
            'tipo_producto' => 'manufacturado',
        ]);

        $productoFinal = Producto::where('nombre', 'PRODUCTO CREADO AUTOMÁTICAMENTE')->first();
        $this->assertDatabaseHas('formula_transformaciones', [
            'producto_final_nombre' => 'PRODUCTO CREADO AUTOMÁTICAMENTE',
            'producto_final_id' => $productoFinal->id,
        ]);
    });

    it('valida que producto_final_nombre es requerido en creación', function () {
        loginComoAdmin();

        livewire(CreateFormulaTransformacion::class)
            ->fillForm([
                'tipo' => 'fabricacion',
                'producto_final_nombre' => '',
                'cantidad_producto_final' => 1,
            ])
            ->call('create')
            ->assertHasFormErrors(['producto_final_nombre']);
    });

    it('una fórmula activa es detectada al crear transformacion', function () {
        loginComoAdmin();
        $productoFinal = crearProductoParaFormula();
        $insumo = crearInsumoParaFormula();

        $formula = FormulaTransformacion::create([
            'producto_final_nombre' => 'Fórmula Detección Test',
            'tipo' => 'fabricacion',
            'producto_final_id' => $productoFinal->id,
            'activo' => true,
        ]);

        FormulaTransformacionDetalle::create([
            'formula_transformacion_id' => $formula->id,
            'producto_id' => $insumo->id,
            'tipo_linea' => 'insumo',
            'cantidad' => 1,
        ]);

        $encontrada = FormulaTransformacion::where('producto_final_id', $productoFinal->id)
            ->where('activo', true)
            ->first();

        expect($encontrada)->not->toBeNull();
        expect($encontrada->id)->toBe($formula->id);
    });
});
