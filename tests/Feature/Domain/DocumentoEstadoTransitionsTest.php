<?php

use App\Enums\CompraEstado;
use App\Enums\VentaEstado;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| DocumentoEstadoTransitionsTest
|
| Verifica las transiciones de estado de documentos.
| Usa los Enums directamente — no necesita modelos en BD para
| verificar qué transiciones son válidas/inválidas.
|--------------------------------------------------------------------------
*/

// ── CompraEstado ──────────────────────────────────────────────────────────────

it('CompraEstado → BORRADOR puede transicionar a REGISTRADA o ANULADA', function () {
    $transiciones = CompraEstado::BORRADOR->validTransitions();

    expect($transiciones)->toContain(CompraEstado::REGISTRADA);
    expect($transiciones)->toContain(CompraEstado::ANULADA);
});

it('CompraEstado → REGISTRADA puede transicionar a PENDIENTE, PAGADA o ANULADA', function () {
    $transiciones = CompraEstado::REGISTRADA->validTransitions();

    expect($transiciones)->toContain(CompraEstado::PENDIENTE);
    expect($transiciones)->toContain(CompraEstado::PAGADA);
    expect($transiciones)->toContain(CompraEstado::ANULADA);
});

it('CompraEstado → PAGADA no permite ninguna transición', function () {
    expect(CompraEstado::PAGADA->validTransitions())->toBeEmpty();
});

it('CompraEstado → ANULADA no permite ninguna transición', function () {
    expect(CompraEstado::ANULADA->validTransitions())->toBeEmpty();
});

it('CompraEstado → PAGADA es estado final', function () {
    expect(CompraEstado::PAGADA->isFinal())->toBeTrue();
});

it('CompraEstado → ANULADA es estado final', function () {
    expect(CompraEstado::ANULADA->isFinal())->toBeTrue();
});

it('CompraEstado → BORRADOR es editable', function () {
    expect(CompraEstado::BORRADOR->isEditable())->toBeTrue();
});

it('CompraEstado → REGISTRADA no es editable', function () {
    expect(CompraEstado::REGISTRADA->isEditable())->toBeFalse();
});

it('no permite transiciones inválidas en compras — PAGADA no puede ir a BORRADOR', function () {
    $transiciones = CompraEstado::PAGADA->validTransitions();

    expect($transiciones)->not->toContain(CompraEstado::BORRADOR);
});

// ── VentaEstado ───────────────────────────────────────────────────────────────

it('VentaEstado → CONFIRMADA no puede volver a BORRADOR', function () {
    $transiciones = VentaEstado::CONFIRMADA->validTransitions();

    expect($transiciones)->not->toContain(VentaEstado::BORRADOR);
});

it('VentaEstado → BORRADOR puede transicionar a CONFIRMADA', function () {
    $transiciones = VentaEstado::BORRADOR->validTransitions();

    expect($transiciones)->toContain(VentaEstado::CONFIRMADA);
});

it('VentaEstado → ANULADA no permite ninguna transición', function () {
    expect(VentaEstado::ANULADA->validTransitions())->toBeEmpty();
});
