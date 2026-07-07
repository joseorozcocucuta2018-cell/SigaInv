<?php

/*
|--------------------------------------------------------------------------
| ResourceSmokeTest.php — Smoke tests para los 30 recursos Filament
|
| Verifica que cada página de listado renderiza sin errores cuando el
| usuario administrador accede. No valida lógica de negocio, solo que
| el componente Livewire se monta correctamente (assertSuccessful).
|
| Organizado por grupo de navegación:
|   - Configuración (8 recursos)
|   - Inventario   (6 recursos)
|   - Compras      (3 recursos)
|   - Ventas       (5 recursos)
|   - Cartera      (2 recursos)
|   - Bancos/Cajas (4 recursos)
|   - Sistema      (2 recursos)
|--------------------------------------------------------------------------
*/

use App\Filament\Resources\AjusteInventarios\Pages\ListAjusteInventarios;
use App\Filament\Resources\Bancos\Pages\ListBancos;
use App\Filament\Resources\Bodegas\Pages\ListBodegas;
use App\Filament\Resources\Cajas\Pages\ListCajas;
use App\Filament\Resources\Categorias\Pages\ListCategorias;
use App\Filament\Resources\ClienteResource\Pages\ListClientes;
use App\Filament\Resources\Compras\Pages\ListCompras;
use App\Filament\Resources\ConteoFisicos\Pages\ListConteoFisicos;
use App\Filament\Resources\Cotizacions\Pages\ListCotizacions;
use App\Filament\Resources\Devoluciones\Pages\ListDevoluciones;
use App\Filament\Resources\Empresas\Pages\ListEmpresas;
use App\Filament\Resources\FormaPagos\Pages\ListFormaPagos;
use App\Filament\Resources\FormulaTransformacions\Pages\ListFormulaTransformacions;
use App\Filament\Resources\Impuestos\Pages\ListImpuestos;
use App\Filament\Resources\Marcas\Pages\ListMarcas;
use App\Filament\Resources\MovimientoBancos\Pages\ListMovimientoBancos;
use App\Filament\Resources\MovimientoCajas\Pages\ListMovimientoCajas;
use App\Filament\Resources\MovimientoInventarios\Pages\ListMovimientoInventarios;
use App\Filament\Resources\Numeracions\Pages\ListNumeracions;
use App\Filament\Resources\PagoClientes\Pages\ListPagoClientes;
use App\Filament\Resources\PagoProveedors\Pages\ListPagoProveedors;
use App\Filament\Resources\Productos\Pages\ListProductos;
use App\Filament\Resources\ProveedorResource\Pages\ListProveedores;
use App\Filament\Resources\Remisions\Pages\ListRemisions;
use App\Filament\Resources\StockBodegas\Pages\ListStockBodegas;
use App\Filament\Resources\Transformacions\Pages\ListTransformacions;
use App\Filament\Resources\Traslados\Pages\ListTraslados;
use App\Filament\Resources\UnidadMedidas\Pages\ListUnidadMedidas;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\Ventas\Pages\ListVentas;
use App\Models\Bodega;
use App\Models\Ciudad;
use App\Models\Departamento;

use function Pest\Livewire\livewire;

// ── Configuración ─────────────────────────────────────────────────────────────

describe('Configuración — Listados', function () {

    it('Empresas: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListEmpresas::class)->assertSuccessful();
    });

    it('Bodegas: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListBodegas::class)->assertSuccessful();
    });

    it('Categorías: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListCategorias::class)->assertSuccessful();
    });

    it('Marcas: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListMarcas::class)->assertSuccessful();
    });

    it('Unidades de Medida: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListUnidadMedidas::class)->assertSuccessful();
    });

    it('Impuestos: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListImpuestos::class)->assertSuccessful();
    });

    it('Formas de Pago: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListFormaPagos::class)->assertSuccessful();
    });

    it('Numeraciones: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListNumeracions::class)->assertSuccessful();
    });
});

// ── Clientes y Proveedores ────────────────────────────────────────────────────

describe('Clientes y Proveedores — Listados', function () {

    it('Clientes: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListClientes::class)->assertSuccessful();
    });

    it('Proveedores: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListProveedores::class)->assertSuccessful();
    });
});

// ── Inventario ────────────────────────────────────────────────────────────────

describe('Inventario — Listados', function () {

    it('Productos: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListProductos::class)->assertSuccessful();
    });

    it('Stock Bodegas: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListStockBodegas::class)->assertSuccessful();
    });

    it('Movimientos Inventario: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListMovimientoInventarios::class)->assertSuccessful();
    });

    it('Ajustes Inventario: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListAjusteInventarios::class)->assertSuccessful();
    });

    it('Conteos Físicos: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListConteoFisicos::class)->assertSuccessful();
    });

    it('Transformaciones: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListTransformacions::class)->assertSuccessful();
    });

    it('Fórmulas de Transformación: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListFormulaTransformacions::class)->assertSuccessful();
    });

    it('Traslados: listado renderiza sin errores', function () {
        loginComoAdmin();

        // Traslados requiere al menos 2 bodegas para acceder
        $depto = Departamento::create(['nombre' => 'Depto Test']);
        $ciudad = Ciudad::create(['nombre' => 'Ciudad Test', 'departamento_id' => $depto->id]);
        Bodega::create(['nombre' => 'Bodega 1', 'direccion1' => 'Calle 1', 'departamento_id' => $depto->id, 'ciudad_id' => $ciudad->id, 'activo' => true]);
        Bodega::create(['nombre' => 'Bodega 2', 'direccion1' => 'Calle 2', 'departamento_id' => $depto->id, 'ciudad_id' => $ciudad->id, 'activo' => true]);

        livewire(ListTraslados::class)->assertSuccessful();
    });
});

// ── Compras ───────────────────────────────────────────────────────────────────

describe('Compras — Listados', function () {

    it('Compras: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListCompras::class)->assertSuccessful();
    });

    it('Proveedores (lista): listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListProveedores::class)->assertSuccessful();
    });
});

// ── Ventas ────────────────────────────────────────────────────────────────────

describe('Ventas — Listados', function () {

    it('Cotizaciones: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListCotizacions::class)->assertSuccessful();
    });

    it('Remisiones: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListRemisions::class)->assertSuccessful();
    });

    it('Ventas: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListVentas::class)->assertSuccessful();
    });

    it('Devoluciones: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListDevoluciones::class)->assertSuccessful();
    });
});

// ── Cartera ───────────────────────────────────────────────────────────────────

describe('Cartera — Listados', function () {

    it('Pagos de Clientes: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListPagoClientes::class)->assertSuccessful();
    });

    it('Pagos de Proveedores: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListPagoProveedors::class)->assertSuccessful();
    });
});

// ── Bancos y Cajas ────────────────────────────────────────────────────────────

describe('Bancos y Cajas — Listados', function () {

    it('Bancos: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListBancos::class)->assertSuccessful();
    });

    it('Movimientos Bancos: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListMovimientoBancos::class)->assertSuccessful();
    });

    it('Cajas: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListCajas::class)->assertSuccessful();
    });

    it('Movimientos Cajas: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListMovimientoCajas::class)->assertSuccessful();
    });
});

// ── Sistema ───────────────────────────────────────────────────────────────────

describe('Sistema — Listados', function () {

    it('Usuarios: listado renderiza sin errores', function () {
        loginComoAdmin();
        livewire(ListUsers::class)->assertSuccessful();
    });
});
