<?php

use App\Http\Controllers\ExcelController;
use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

Route::middleware(['auth:web,cliente'])->prefix('pdf')->name('pdf.')->group(function () {
    Route::get('/venta/{venta}', [PdfController::class, 'venta'])->name('venta');
    Route::get('/conteo/{conteo}', [PdfController::class, 'conteo'])->name('conteo');
    Route::get('/conteo/{conteo}/guia', [PdfController::class, 'guiaConteo'])->name('conteo.guia');
    Route::get('/conteo/{conteo}/diferencias', [PdfController::class, 'diferenciasConteo'])->name('conteo.diferencias');
    Route::get('/remision/{remision}', [PdfController::class, 'remision'])->name('remision');
    Route::get('/cotizacion/{cotizacion}', [PdfController::class, 'cotizacion'])->name('cotizacion');
    Route::get('/kardex', [PdfController::class, 'kardex'])->name('kardex');
    Route::get('/cartera', [PdfController::class, 'cartera'])->name('cartera');
    Route::get('/rentabilidad', [PdfController::class, 'rentabilidad'])->name('rentabilidad');
    Route::get('/reporte-ventas', [PdfController::class, 'reporteVentas'])->name('reporte-ventas');
    Route::get('/reporte-compras', [PdfController::class, 'reporteCompras'])->name('reporte-compras');
    Route::get('/inventario-valorizado', [PdfController::class, 'inventarioValorizado'])->name('inventario-valorizado');
    Route::get('/productos-sin-movimiento', [PdfController::class, 'productosSinMovimiento'])->name('productos-sin-movimiento');
    Route::get('/ventas-vendedor', [PdfController::class, 'ventasVendedor'])->name('ventas-vendedor');
});

Route::middleware(['auth:web,cliente'])->prefix('excel')->name('excel.')->group(function () {
    Route::get('/kardex', [ExcelController::class, 'kardex'])->name('kardex');
    Route::get('/cartera', [ExcelController::class, 'cartera'])->name('cartera');
    Route::get('/rentabilidad', [ExcelController::class, 'rentabilidad'])->name('rentabilidad');
    Route::get('/reporte-ventas', [ExcelController::class, 'reporteVentas'])->name('reporte-ventas');
    Route::get('/reporte-compras', [ExcelController::class, 'reporteCompras'])->name('reporte-compras');
    Route::get('/inventario-valorizado', [ExcelController::class, 'inventarioValorizado'])->name('inventario-valorizado');
    Route::get('/productos-sin-movimiento', [ExcelController::class, 'productosSinMovimiento'])->name('productos-sin-movimiento');
    Route::get('/ventas-vendedor', [ExcelController::class, 'ventasVendedor'])->name('ventas-vendedor');
});
