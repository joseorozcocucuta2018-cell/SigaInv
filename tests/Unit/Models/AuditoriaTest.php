<?php

/*
|--------------------------------------------------------------------------
| AuditoriaTest.php — Tests del modelo Auditoria
| Cubre el cast a enum: operacion
|--------------------------------------------------------------------------
*/

use App\Enums\AuditoriaOperacion;
use App\Models\Auditoria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Modelo Auditoria', function () {

    it('castea operacion como AuditoriaOperacion enum', function () {
        $auditoria = Auditoria::factory()->create(['operacion' => AuditoriaOperacion::INSERT]);
        expect($auditoria->operacion)
            ->toBeInstanceOf(AuditoriaOperacion::class)
            ->toBe(AuditoriaOperacion::INSERT);
    });

    it('puede asignar todos los valores de AuditoriaOperacion', function (AuditoriaOperacion $valor) {
        $auditoria = Auditoria::factory()->create(['operacion' => $valor]);
        expect($auditoria->operacion)->toBe($valor);
    })->with([
        'INSERT' => [AuditoriaOperacion::INSERT],
        'UPDATE' => [AuditoriaOperacion::UPDATE],
        'DELETE' => [AuditoriaOperacion::DELETE],
    ]);

    it('factory state update setea operacion y datos_anteriores', function () {
        $auditoria = Auditoria::factory()->update()->create();
        expect($auditoria->operacion)->toBe(AuditoriaOperacion::UPDATE)
            ->and($auditoria->datos_anteriores)->toBe(['campo' => 'anterior']);
    });

    it('pertenece a un usuario', function () {
        $usuario = User::factory()->create();
        $auditoria = Auditoria::factory()->create(['usuario_id' => $usuario->id]);

        expect($auditoria->usuario)->toBeInstanceOf(User::class)
            ->and($auditoria->usuario->id)->toBe($usuario->id);
    });
});
