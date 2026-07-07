<?php

use App\Enums\NumeracionEstado;
use App\Models\Numeracion;
use App\Services\NumeracionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| NumeracionServiceTest
|
| Verifica el comportamiento del servicio de consecutivos:
| - Genera el número correcto con prefijo y ceros
| - Incrementa el consecutivo en cada llamada
| - Lanza excepción si no hay numeración configurada
| - Lanza excepción si el rango está agotado
| - No permite reiniciar si ya tiene documentos emitidos
|--------------------------------------------------------------------------
*/

// ── Helper: crear numeración de prueba ────────────────────────────────────────
function crearNumeracion(array $extra = []): Numeracion
{
    return Numeracion::create(array_merge([
        'tipo_documento' => 'venta',
        'resolucion_numero' => 'TEST-00001',
        'prefijo' => 'VEN-',
        'consecutivo_desde' => 1,
        'consecutivo_hasta' => 100,
        'consecutivo_actual' => 0,
        'anno' => now()->year,
        'estado' => NumeracionEstado::ACTIVO,
    ], $extra));
}

// ── Generación de números ─────────────────────────────────────────────────────

it('NumeracionService → genera el primer número con prefijo y ceros correctos', function () {
    crearNumeracion(['prefijo' => 'VEN-']);

    $resultado = NumeracionService::obtenerSiguienteNumero('venta');

    expect($resultado['numero'])->toBe('VEN-000001');
});

it('NumeracionService → incrementa el consecutivo en cada llamada', function () {
    crearNumeracion(['prefijo' => 'VEN-']);

    $r1 = NumeracionService::obtenerSiguienteNumero('venta');
    $r2 = NumeracionService::obtenerSiguienteNumero('venta');
    $r3 = NumeracionService::obtenerSiguienteNumero('venta');

    expect($r1['numero'])->toBe('VEN-000001');
    expect($r2['numero'])->toBe('VEN-000002');
    expect($r3['numero'])->toBe('VEN-000003');
});

it('NumeracionService → actualiza consecutivo_actual en la base de datos', function () {
    crearNumeracion();

    NumeracionService::obtenerSiguienteNumero('venta');
    NumeracionService::obtenerSiguienteNumero('venta');

    $numeracion = Numeracion::where('tipo_documento', 'venta')->first();
    expect($numeracion->consecutivo_actual)->toBe(2);
});

it('NumeracionService → retorna la instancia de Numeracion en el resultado', function () {
    crearNumeracion();

    $resultado = NumeracionService::obtenerSiguienteNumero('venta');

    expect($resultado['numeracion'])->toBeInstanceOf(Numeracion::class);
});

// ── Errores esperados ─────────────────────────────────────────────────────────

it('NumeracionService → lanza excepción si no hay numeración configurada para el tipo', function () {
    // No hay ninguna numeración creada

    expect(fn () => NumeracionService::obtenerSiguienteNumero('venta'))
        ->toThrow(Exception::class, 'No hay numeración configurada');
});

it('NumeracionService → lanza excepción si la numeración está inactiva', function () {
    crearNumeracion(['estado' => NumeracionEstado::INACTIVO]);

    expect(fn () => NumeracionService::obtenerSiguienteNumero('venta'))
        ->toThrow(Exception::class, 'No hay numeración configurada');
});

it('NumeracionService → lanza excepción si el rango está agotado', function () {
    crearNumeracion([
        'consecutivo_desde' => 1,
        'consecutivo_hasta' => 3,
        'consecutivo_actual' => 3,  // Ya en el límite
    ]);

    expect(fn () => NumeracionService::obtenerSiguienteNumero('venta'))
        ->toThrow(Exception::class, 'Se ha agotado el rango');
});

it('NumeracionService → tipos de documento distintos son independientes', function () {
    crearNumeracion(['tipo_documento' => 'venta',        'prefijo' => 'VEN-']);
    crearNumeracion(['tipo_documento' => 'nota_credito', 'prefijo' => 'NC-']);

    $venta = NumeracionService::obtenerSiguienteNumero('venta');
    $notaCredito = NumeracionService::obtenerSiguienteNumero('nota_credito');

    expect($venta['numero'])->toBe('VEN-000001');
    expect($notaCredito['numero'])->toBe('NC-000001');

    // Ambos consecutivos deben ser 1 (independientes)
    expect(Numeracion::where('tipo_documento', 'venta')->value('consecutivo_actual'))->toBe(1);
    expect(Numeracion::where('tipo_documento', 'nota_credito')->value('consecutivo_actual'))->toBe(1);
});

// ── estaEnUso ─────────────────────────────────────────────────────────────────

it('NumeracionService → estaEnUso retorna false si no se ha generado ningún número', function () {
    $numeracion = crearNumeracion(['consecutivo_actual' => 0]);

    expect(NumeracionService::estaEnUso($numeracion))->toBeFalse();
});

it('NumeracionService → estaEnUso retorna true si ya se generó al menos un número', function () {
    $numeracion = crearNumeracion(['consecutivo_actual' => 1]);

    expect(NumeracionService::estaEnUso($numeracion))->toBeTrue();
});

// ── reiniciarConsecutivo ──────────────────────────────────────────────────────

it('NumeracionService → no permite reiniciar si ya tiene documentos emitidos', function () {
    $numeracion = crearNumeracion(['consecutivo_actual' => 5]);

    expect(fn () => NumeracionService::reiniciarConsecutivo($numeracion, 1))
        ->toThrow(Exception::class, 'No se puede reiniciar');
});

it('NumeracionService → no permite reiniciar con valor menor al rango configurado', function () {
    $numeracion = crearNumeracion([
        'consecutivo_desde' => 100,
        'consecutivo_actual' => 0,
    ]);

    expect(fn () => NumeracionService::reiniciarConsecutivo($numeracion, 50))
        ->toThrow(Exception::class, 'no puede ser menor al rango');
});
