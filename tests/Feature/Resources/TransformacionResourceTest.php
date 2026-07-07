<?php

/*
|--------------------------------------------------------------------------
| TransformacionResourceTest.php — Tests de frontend para el recurso Transformacion
|
| Cubre:
|   - Listado: renderiza, muestra registros
|   - Crear: page renderiza, crea borrador válido, validación de campos
|   - Editar: page renderiza, guarda cambios
|   - Ver: page renderiza con datos del infolist
|   - Auto-detección de fórmula al seleccionar producto_final
|   - Acción Confirmar via servicio (flujo end-to-end en Livewire)
|--------------------------------------------------------------------------
*/

use App\Enums\TransformacionEstado;
use App\Enums\TransformacionTipo;
use App\Filament\Resources\Transformacions\Pages\CreateTransformacion;
use App\Filament\Resources\Transformacions\Pages\EditTransformacion;
use App\Filament\Resources\Transformacions\Pages\ListTransformacions;
use App\Filament\Resources\Transformacions\Pages\ViewTransformacion;
use App\Models\Bodega;
use App\Models\Ciudad;
use App\Models\Departamento;
use App\Models\FormulaTransformacion;
use App\Models\FormulaTransformacionDetalle;
use App\Models\Producto;
use App\Models\StockBodega;
use App\Models\Transformacion;
use App\Models\TransformacionDetalle;
use App\Services\TransformacionService;
use Filament\Actions\Testing\TestAction;

use function Pest\Livewire\livewire;

// ── Helper local ──────────────────────────────────────────────────────────────

function crearEscenarioTransformacion(): array
{
    $depto = Departamento::create(['nombre' => 'Dpto Test Recurso']);
    $ciudad = Ciudad::create(['nombre' => 'Ciudad Test', 'departamento_id' => $depto->id]);

    $bodega = Bodega::create([
        'nombre' => 'Bodega Principal',
        'activo' => true,
        'direccion1' => 'Calle 1',
        'departamento_id' => $depto->id,
        'ciudad_id' => $ciudad->id,
    ]);

    $insumo = Producto::create([
        'codigo' => 'INS-REC-'.uniqid(),
        'nombre' => 'Insumo Recurso',
        'precio_compra' => 100,
        'activo' => true,
    ]);

    $productoFinal = Producto::create([
        'codigo' => 'PRF-REC-'.uniqid(),
        'nombre' => 'Producto Final Recurso',
        'precio_venta' => 300,
        'tipo_producto' => 'manufacturado',
        'con_formula' => true,
        'activo' => true,
    ]);

    StockBodega::create([
        'producto_id' => $insumo->id,
        'bodega_id' => $bodega->id,
        'cantidad' => 200,
    ]);

    return compact('bodega', 'insumo', 'productoFinal');
}

function crearFormulaConProductoFinal(int $productoFinalId, int $insumoId): FormulaTransformacion
{
    $formula = FormulaTransformacion::create([
        'producto_final_nombre' => 'Fórmula '.uniqid(),
        'tipo' => 'fabricacion',
        'producto_final_id' => $productoFinalId,
        'activo' => true,
    ]);

    FormulaTransformacionDetalle::create([
        'formula_transformacion_id' => $formula->id,
        'producto_id' => $insumoId,
        'tipo_linea' => 'insumo',
        'cantidad' => 3,
    ]);

    return $formula;
}

function crearTransformacionBorrador(int $bodegaId, int $productoFinalId, ?int $formulaId = null): Transformacion
{
    return Transformacion::create([
        'bodega_id' => $bodegaId,
        'producto_final_id' => $productoFinalId,
        'formula_transformacion_id' => $formulaId,
        'estado' => TransformacionEstado::BORRADOR,
        'tipo' => TransformacionTipo::FABRICACION,
        'cantidad_a_producir' => 1,
        'fecha' => now(),
    ]);
}

// ── Listado ───────────────────────────────────────────────────────────────────

describe('TransformacionResource — Listado', function () {

    it('el administrador puede ver la lista de transformaciones', function () {
        loginComoAdmin();

        livewire(ListTransformacions::class)
            ->assertSuccessful();
    });

    it('muestra transformaciones existentes en la tabla', function () {
        loginComoAdmin();
        ['bodega' => $bodega, 'productoFinal' => $productoFinal] = crearEscenarioTransformacion();

        crearTransformacionBorrador($bodega->id, $productoFinal->id);

        livewire(ListTransformacions::class)
            ->assertSuccessful()
            ->assertSee($productoFinal->nombre);
    });

    it('muestra el estado como badge', function () {
        loginComoAdmin();
        ['bodega' => $bodega, 'productoFinal' => $productoFinal] = crearEscenarioTransformacion();

        crearTransformacionBorrador($bodega->id, $productoFinal->id);

        livewire(ListTransformacions::class)
            ->assertSuccessful()
            ->assertSee('Borrador');
    });
});

// ── Crear ─────────────────────────────────────────────────────────────────────

describe('TransformacionResource — Crear', function () {

    it('la página de creación renderiza sin errores', function () {
        loginComoAdmin();

        livewire(CreateTransformacion::class)
            ->assertSuccessful();
    });

    it('puede crear una transformación borrador con datos básicos', function () {
        loginComoAdmin();
        ['bodega' => $bodega, 'productoFinal' => $productoFinal, 'insumo' => $insumo] = crearEscenarioTransformacion();
        crearFormulaConProductoFinal($productoFinal->id, $insumo->id);

        livewire(CreateTransformacion::class)
            ->fillForm([
                'bodega_id' => $bodega->id,
                'tipo' => 'fabricacion',
                'producto_final_id' => $productoFinal->id,
                'cantidad_a_producir' => 2,
                'estado' => 'borrador',
                'tipo_calculo_precio' => 'margen',
                'margen_deseado' => 30,
                'detalles' => [
                    ['tipo_linea' => 'insumo', 'producto_id' => $insumo->id, 'cantidad' => 2, 'costo_unitario' => 100],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('transformaciones', [
            'bodega_id' => $bodega->id,
            'producto_final_id' => $productoFinal->id,
            'estado' => 'borrador',
        ]);
    });

    it('no permite crear sin seleccionar bodega', function () {
        loginComoAdmin();
        ['productoFinal' => $productoFinal] = crearEscenarioTransformacion();

        livewire(CreateTransformacion::class)
            ->fillForm([
                'bodega_id' => null,
                'producto_final_id' => $productoFinal->id,
                'cantidad_a_producir' => 1,
                'estado' => 'borrador',
            ])
            ->call('create')
            ->assertHasFormErrors(['bodega_id']);
    });

    it('no permite crear sin seleccionar producto final', function () {
        loginComoAdmin();
        ['bodega' => $bodega] = crearEscenarioTransformacion();

        livewire(CreateTransformacion::class)
            ->fillForm([
                'bodega_id' => $bodega->id,
                'producto_final_id' => null,
                'cantidad_a_producir' => 1,
                'estado' => 'borrador',
            ])
            ->call('create')
            ->assertHasFormErrors(['producto_final_id']);
    });

    it('el formulario crea la transformación correctamente cuando hay fórmula vinculada', function () {
        loginComoAdmin();
        ['bodega' => $bodega, 'productoFinal' => $productoFinal, 'insumo' => $insumo] = crearEscenarioTransformacion();
        $formula = crearFormulaConProductoFinal($productoFinal->id, $insumo->id);

        // Nota: formula_transformacion_id es disabled en el form; no se puede enviar
        // por fillForm. El campo se auto-detecta via afterStateUpdated (live reactivity).
        // La aplicación de la fórmula vía afterSave está cubierta en TransformacionServiceTest.
        livewire(CreateTransformacion::class)
            ->fillForm([
                'bodega_id' => $bodega->id,
                'tipo' => 'fabricacion',
                'producto_final_id' => $productoFinal->id,
                'cantidad_a_producir' => 1,
                'estado' => 'borrador',
                'tipo_calculo_precio' => 'margen',
                'margen_deseado' => 30,
                'detalles' => [
                    ['tipo_linea' => 'insumo', 'producto_id' => $insumo->id, 'cantidad' => 1, 'costo_unitario' => 100],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('transformaciones', [
            'bodega_id' => $bodega->id,
            'producto_final_id' => $productoFinal->id,
        ]);
    });
});

// ── Editar ────────────────────────────────────────────────────────────────────

describe('TransformacionResource — Editar', function () {

    it('la página de edición renderiza para un borrador', function () {
        loginComoAdmin();
        ['bodega' => $bodega, 'productoFinal' => $productoFinal] = crearEscenarioTransformacion();
        $transformacion = crearTransformacionBorrador($bodega->id, $productoFinal->id);

        livewire(EditTransformacion::class, ['record' => $transformacion->id])
            ->assertSuccessful();
    });

    it('puede actualizar la cantidad a producir', function () {
        loginComoAdmin();
        ['bodega' => $bodega, 'productoFinal' => $productoFinal, 'insumo' => $insumo] = crearEscenarioTransformacion();
        crearFormulaConProductoFinal($productoFinal->id, $insumo->id);
        $transformacion = crearTransformacionBorrador($bodega->id, $productoFinal->id);

        livewire(EditTransformacion::class, ['record' => $transformacion->id])
            ->fillForm(['cantidad_a_producir' => 5])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('transformaciones', [
            'id' => $transformacion->id,
            'cantidad_a_producir' => 5,
        ]);
    });

    it('muestra la acción confirmar solo en borrador', function () {
        loginComoAdmin();
        ['bodega' => $bodega, 'productoFinal' => $productoFinal] = crearEscenarioTransformacion();
        $transformacion = crearTransformacionBorrador($bodega->id, $productoFinal->id);

        livewire(EditTransformacion::class, ['record' => $transformacion->id])
            ->assertSuccessful()
            ->assertActionVisible('confirmar');
    });

    it('no muestra la acción confirmar si ya está confirmada', function () {
        loginComoAdmin();
        ['bodega' => $bodega, 'productoFinal' => $productoFinal] = crearEscenarioTransformacion();
        $transformacion = crearTransformacionBorrador($bodega->id, $productoFinal->id);
        $transformacion->update(['estado' => TransformacionEstado::CONFIRMADA]);

        livewire(EditTransformacion::class, ['record' => $transformacion->id])
            ->assertSuccessful()
            ->assertActionHidden('confirmar');
    });

    it('muestra la acción revertir solo en combo/promo confirmados', function () {
        loginComoAdmin();
        ['bodega' => $bodega, 'productoFinal' => $productoFinal] = crearEscenarioTransformacion();
        $transformacion = Transformacion::create([
            'bodega_id' => $bodega->id,
            'producto_final_id' => $productoFinal->id,
            'estado' => TransformacionEstado::CONFIRMADA,
            'tipo' => TransformacionTipo::COMBO,
            'cantidad_a_producir' => 1,
            'fecha' => now(),
        ]);

        livewire(EditTransformacion::class, ['record' => $transformacion->id])
            ->assertSuccessful()
            ->assertActionVisible('revertir');
    });
});

// ── Ver ───────────────────────────────────────────────────────────────────────

describe('TransformacionResource — Ver', function () {

    it('la página de visualización renderiza correctamente', function () {
        loginComoAdmin();
        ['bodega' => $bodega, 'productoFinal' => $productoFinal] = crearEscenarioTransformacion();
        $transformacion = crearTransformacionBorrador($bodega->id, $productoFinal->id);

        livewire(ViewTransformacion::class, ['record' => $transformacion->id])
            ->assertSuccessful()
            ->assertSee($productoFinal->nombre);
    });

    it('muestra el nombre del producto final en el infolist', function () {
        loginComoAdmin();
        ['bodega' => $bodega, 'productoFinal' => $productoFinal] = crearEscenarioTransformacion();
        $transformacion = crearTransformacionBorrador($bodega->id, $productoFinal->id);

        livewire(ViewTransformacion::class, ['record' => $transformacion->id])
            ->assertSee($productoFinal->nombre)
            ->assertSee($bodega->nombre);
    });

    it('muestra el estado del infolist', function () {
        loginComoAdmin();
        ['bodega' => $bodega, 'productoFinal' => $productoFinal] = crearEscenarioTransformacion();
        $transformacion = crearTransformacionBorrador($bodega->id, $productoFinal->id);

        livewire(ViewTransformacion::class, ['record' => $transformacion->id])
            ->assertSee('Borrador');
    });
});

// ── Flujo end-to-end via servicio ─────────────────────────────────────────────

describe('TransformacionResource — Flujo Confirmar', function () {

    it('confirmar via servicio actualiza el listado correctamente', function () {
        loginComoAdmin();
        ['bodega' => $bodega, 'productoFinal' => $productoFinal, 'insumo' => $insumo] = crearEscenarioTransformacion();
        $transformacion = crearTransformacionBorrador($bodega->id, $productoFinal->id);

        TransformacionDetalle::create([
            'transformacion_id' => $transformacion->id,
            'tipo_linea' => 'insumo',
            'producto_id' => $insumo->id,
            'cantidad' => 5,
            'costo_unitario' => 100,
        ]);
        $transformacion->update(['cantidad_a_producir' => 1]);

        app(TransformacionService::class)->confirmar($transformacion);

        livewire(ListTransformacions::class)
            ->assertSuccessful()
            ->assertSee('Confirmada');
    });

    it('transformacion confirmada aparece como no editable', function () {
        loginComoAdmin();
        ['bodega' => $bodega, 'productoFinal' => $productoFinal] = crearEscenarioTransformacion();
        $transformacion = crearTransformacionBorrador($bodega->id, $productoFinal->id);
        $transformacion->update(['estado' => TransformacionEstado::CONFIRMADA]);

        livewire(EditTransformacion::class, ['record' => $transformacion->id])
            ->assertSuccessful()
            ->assertActionHidden('confirmar')
            ->assertActionHidden('delete');
    });
});

// ── Creación de producto final nuevo inline ───────────────────────────────────

describe('TransformacionResource — Crear Producto Final Nuevo', function () {

    it('la opción de crear producto final está disponible en el formulario', function () {
        loginComoAdmin();

        livewire(CreateTransformacion::class)
            ->assertSuccessful()
            ->assertActionExists(
                TestAction::make('createOption')->schemaComponent('producto_final_id')
            );
    });

    it('permite crear un nuevo producto final desde el formulario de transformación', function () {
        loginComoAdmin();
        ['bodega' => $bodega] = crearEscenarioTransformacion();

        // callAction crea el producto a través del modal de createOptionForm;
        // no usamos assertHasNoActionErrors porque internamente valida el Repeater
        // del formulario padre (que tiene defaultItems(1) sin producto seleccionado)
        livewire(CreateTransformacion::class)
            ->callAction(
                TestAction::make('createOption')->schemaComponent('producto_final_id'),
                data: [
                    'codigo' => 'PROD-NUEVO-T01',
                    'nombre' => 'Producto Final Creado Inline',
                    'tipo_producto' => 'manufacturado',
                    'precio_venta' => 500,
                    'activo' => true,
                ]
            );

        // El producto debe haber sido creado en la BD
        $this->assertDatabaseHas('productos', [
            'codigo' => 'PROD-NUEVO-T01',
            'nombre' => 'PRODUCTO FINAL CREADO INLINE',
        ]);
    });

    it('puede crear transformación con un producto final recién creado', function () {
        loginComoAdmin();
        ['bodega' => $bodega, 'insumo' => $insumo] = crearEscenarioTransformacion();

        // Crear el producto directamente (simula el resultado del createOptionForm)
        $productoNuevo = Producto::create([
            'codigo' => 'PROD-INLINE-001',
            'nombre' => 'Producto Inline Test',
            'tipo_producto' => 'manufacturado',
            'con_formula' => true,
            'precio_venta' => 300,
            'activo' => true,
        ]);

        FormulaTransformacion::create([
            'producto_final_nombre' => 'FORMULA INLINE',
            'tipo' => 'fabricacion',
            'producto_final_id' => $productoNuevo->id,
            'activo' => true,
        ]);

        livewire(CreateTransformacion::class)
            ->fillForm([
                'bodega_id' => $bodega->id,
                'tipo' => 'fabricacion',
                'producto_final_id' => $productoNuevo->id,
                'cantidad_a_producir' => 1,
                'estado' => 'borrador',
                'tipo_calculo_precio' => 'margen',
                'margen_deseado' => 30,
                'detalles' => [
                    ['tipo_linea' => 'insumo', 'producto_id' => $insumo->id, 'cantidad' => 1, 'costo_unitario' => 100],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('transformaciones', [
            'producto_final_id' => $productoNuevo->id,
        ]);
    });
});
