<?php

/*
|--------------------------------------------------------------------------
| EmpresaTest.php — Tests unitarios del modelo Empresa
| Cubre los casts a enums: tipo_persona, regimen_tributario
|--------------------------------------------------------------------------
*/

use App\Enums\EmpresaRegimenTributario;
use App\Enums\EmpresaTipoPersona;
use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Modelo Empresa', function () {

    it('tiene los campos fillable correctos', function () {
        expect((new Empresa)->getFillable())
            ->toContain('razon_social')
            ->toContain('nit')
            ->toContain('digito_verificacion')
            ->toContain('tipo_persona')
            ->toContain('regimen_tributario');
    });

    it('castea tipo_persona como EmpresaTipoPersona enum', function () {
        $empresa = Empresa::factory()->create(['tipo_persona' => EmpresaTipoPersona::NATURAL]);
        expect($empresa->tipo_persona)
            ->toBeInstanceOf(EmpresaTipoPersona::class)
            ->toBe(EmpresaTipoPersona::NATURAL);
    });

    it('castea regimen_tributario como EmpresaRegimenTributario enum', function () {
        $empresa = Empresa::factory()->create(['regimen_tributario' => EmpresaRegimenTributario::GRAN_CONTRIBUYENTE]);
        expect($empresa->regimen_tributario)
            ->toBeInstanceOf(EmpresaRegimenTributario::class)
            ->toBe(EmpresaRegimenTributario::GRAN_CONTRIBUYENTE);
    });

    it('puede asignar todos los valores de EmpresaTipoPersona', function (EmpresaTipoPersona $valor) {
        $empresa = Empresa::factory()->create(['tipo_persona' => $valor]);
        expect($empresa->tipo_persona)->toBe($valor);
    })->with([
        'natural' => [EmpresaTipoPersona::NATURAL],
        'juridica' => [EmpresaTipoPersona::JURIDICA],
    ]);

    it('puede asignar todos los valores de EmpresaRegimenTributario', function (EmpresaRegimenTributario $valor) {
        $empresa = Empresa::factory()->create(['regimen_tributario' => $valor]);
        expect($empresa->regimen_tributario)->toBe($valor);
    })->with([
        'simplificado' => [EmpresaRegimenTributario::SIMPLIFICADO],
        'comun' => [EmpresaRegimenTributario::COMUN],
        'gran_contribuyente' => [EmpresaRegimenTributario::GRAN_CONTRIBUYENTE],
    ]);

    it('EmpresaRegimenTributario::responsableIva retorna false solo para SIMPLIFICADO', function () {
        expect(EmpresaRegimenTributario::SIMPLIFICADO->responsableIva())->toBeFalse()
            ->and(EmpresaRegimenTributario::COMUN->responsableIva())->toBeTrue()
            ->and(EmpresaRegimenTributario::GRAN_CONTRIBUYENTE->responsableIva())->toBeTrue();
    });

    it('factory state simplificado setea regimen y responsable_iva correctamente', function () {
        $empresa = Empresa::factory()->simplificado()->create();
        expect($empresa->regimen_tributario)->toBe(EmpresaRegimenTributario::SIMPLIFICADO)
            ->and($empresa->responsable_iva)->toBeFalse();
    });
});
