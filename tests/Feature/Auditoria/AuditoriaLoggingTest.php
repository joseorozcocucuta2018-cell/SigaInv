<?php

use App\Models\AuditoriaDocumento;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| AuditoriaLoggingTest
|
| Verifica que los Observers de auditoría registran cambios correctamente.
| No usa factories — crea modelos directamente.
|--------------------------------------------------------------------------
*/

it('actualizar producto genera registro de auditoria', function () {
    $producto = Producto::create([
        'codigo' => 'AUD-'.uniqid(),
        'nombre' => 'Producto A',
        'precio_venta' => 100,
        'activo' => true,
    ]);

    $producto->update(['nombre' => 'Producto B']);

    $registro = AuditoriaDocumento::where('documento_tipo', 'producto')->first();

    expect($registro)->not->toBeNull();
});

it('crear producto genera registro de auditoria', function () {
    $producto = Producto::create([
        'codigo' => 'AUD-'.uniqid(),
        'nombre' => 'Producto Nuevo',
        'precio_venta' => 200,
        'activo' => true,
    ]);

    $registro = AuditoriaDocumento::where('documento_tipo', 'producto')
        ->where('documento_id', $producto->id)
        ->first();

    expect($registro)->not->toBeNull();
});
