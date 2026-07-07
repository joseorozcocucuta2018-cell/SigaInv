<?php

use App\Http\Controllers\Pos\AuthController;
use App\Http\Controllers\Pos\BodegaController;
use App\Http\Controllers\Pos\CajaController;
use App\Http\Controllers\Pos\ClienteController;
use App\Http\Controllers\Pos\EmpresaController;
use App\Http\Controllers\Pos\FormaPagoController;
use App\Http\Controllers\Pos\ProductoController;
use App\Http\Controllers\Pos\RemisionController;
use App\Http\Controllers\Pos\StockController;
use App\Http\Controllers\Pos\TurnoController;
use App\Http\Controllers\Pos\VentaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('pos/api')->group(function (): void {
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'pos.activo'])->group(function (): void {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::get('empresa', [EmpresaController::class, 'show']);

        Route::get('productos', [ProductoController::class, 'index']);
        Route::get('productos/{producto}', [ProductoController::class, 'show']);

        Route::get('stock', [StockController::class, 'index']);

        Route::get('clientes', [ClienteController::class, 'index']);
        Route::get('clientes/{cliente}', [ClienteController::class, 'show']);
        Route::post('clientes', [ClienteController::class, 'store']);

        Route::get('bodegas', [BodegaController::class, 'index']);

        Route::get('formas-pago', [FormaPagoController::class, 'index']);

        Route::get('turnos/activo', [TurnoController::class, 'activo']);
        Route::post('turnos', [TurnoController::class, 'abrir']);
        Route::post('turnos/cerrar', [TurnoController::class, 'cerrar']);
        Route::get('turnos/{turno}/resumen', [TurnoController::class, 'resumen']);

        Route::get('ventas/{id}', [VentaController::class, 'show']);
        Route::post('ventas', [VentaController::class, 'store']);

        Route::get('remisiones/{id}', [RemisionController::class, 'show']);
        Route::post('remisiones', [RemisionController::class, 'store']);

        Route::get('cajas', [CajaController::class, 'index']);
        Route::get('cajas/{caja}', [CajaController::class, 'show']);
    });
});
