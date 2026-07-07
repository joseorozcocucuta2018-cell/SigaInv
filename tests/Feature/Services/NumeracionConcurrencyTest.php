<?php

use App\Enums\NumeracionEstado;
use App\Models\Numeracion;
use App\Services\NumeracionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| NumeracionConcurrencyTest
|
| Verifica que llamadas secuenciales al servicio generan números distintos.
| NOTA: PHP single-process no puede probar concurrencia real.
|       Este test verifica que el mecanismo de incremento funciona
|       correctamente en llamadas sucesivas (que es lo que protege lockForUpdate).
|--------------------------------------------------------------------------
*/

it('dos llamadas sucesivas generan números distintos', function () {
    Numeracion::create([
        'tipo_documento' => 'venta',
        'resolucion_numero' => 'TEST-00001',
        'prefijo' => 'VEN',
        'anno' => now()->year,
        'consecutivo_desde' => 1,
        'consecutivo_hasta' => 999999,
        'consecutivo_actual' => 0,
        'estado' => NumeracionEstado::ACTIVO,
    ]);

    $result1 = NumeracionService::obtenerSiguienteNumero('venta');
    $result2 = NumeracionService::obtenerSiguienteNumero('venta');

    expect($result1['numero'])->not->toEqual($result2['numero']);
});

it('el consecutivo_actual se incrementa correctamente en cada llamada', function () {
    Numeracion::create([
        'tipo_documento' => 'venta',
        'resolucion_numero' => 'TEST-00001',
        'prefijo' => 'VEN',
        'anno' => now()->year,
        'consecutivo_desde' => 1,
        'consecutivo_hasta' => 999999,
        'consecutivo_actual' => 0,
        'estado' => NumeracionEstado::ACTIVO,
    ]);

    NumeracionService::obtenerSiguienteNumero('venta');
    NumeracionService::obtenerSiguienteNumero('venta');
    NumeracionService::obtenerSiguienteNumero('venta');

    $numeracion = Numeracion::where('tipo_documento', 'venta')->first();
    expect($numeracion->consecutivo_actual)->toBe(3);
});

it('diez llamadas sucesivas producen diez números únicos', function () {
    Numeracion::create([
        'tipo_documento' => 'venta',
        'resolucion_numero' => 'TEST-00001',
        'prefijo' => 'VEN',
        'anno' => now()->year,
        'consecutivo_desde' => 1,
        'consecutivo_hasta' => 999999,
        'consecutivo_actual' => 0,
        'estado' => NumeracionEstado::ACTIVO,
    ]);

    $numeros = [];
    for ($i = 0; $i < 10; $i++) {
        $numeros[] = NumeracionService::obtenerSiguienteNumero('venta')['numero'];
    }

    // Todos deben ser únicos
    expect(count(array_unique($numeros)))->toBe(10);
});
