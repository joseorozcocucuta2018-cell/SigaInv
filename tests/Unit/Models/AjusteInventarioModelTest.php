<?php

/*
|--------------------------------------------------------------------------
| AjusteInventarioModelTest.php — Tests del modelo AjusteInventario
| Cubre el cast boolean: es_saldo_inicial
|--------------------------------------------------------------------------
*/

use App\Enums\AjusteEstado;
use App\Models\AjusteInventario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Modelo AjusteInventario — cast es_saldo_inicial', function () {

    it('castea es_saldo_inicial=1 como boolean true', function () {
        $ajuste = AjusteInventario::factory()->create([
            'es_saldo_inicial' => true,
            'estado' => AjusteEstado::CONFIRMADO,
        ]);

        expect($ajuste->es_saldo_inicial)->toBeBool()->toBeTrue();
    });

    it('castea es_saldo_inicial=0 como boolean false', function () {
        $ajuste = AjusteInventario::factory()->create([
            'es_saldo_inicial' => false,
        ]);

        expect($ajuste->es_saldo_inicial)->toBeBool()->toBeFalse();
    });

    it('default es false cuando no se especifica', function () {
        $ajuste = AjusteInventario::factory()->create();

        expect($ajuste->es_saldo_inicial)->toBeBool()->toBeFalse();
    });
});
