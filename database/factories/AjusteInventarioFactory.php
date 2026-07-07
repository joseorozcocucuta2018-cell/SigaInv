<?php

namespace Database\Factories;

use App\Enums\AjusteEstado;
use App\Enums\MotivoAjuste;
use App\Models\Bodega;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AjusteInventarioFactory extends Factory
{
    protected $table = 'ajustes_inventario';

    public function definition(): array
    {
        return [
            'numero' => 'AJU-'.fake()->unique()->numerify('#####'),
            'bodega_id' => Bodega::factory(),
            'usuario_id' => User::factory(),
            'fecha' => now(),
            'motivo' => MotivoAjuste::AJUSTE_INICIAL,
            'estado' => AjusteEstado::BORRADOR,
            'es_saldo_inicial' => false,
            'observacion' => null,
            'confirmado_en' => null,
        ];
    }

    public function saldoInicial(): static
    {
        return $this->state(fn (): array => [
            'es_saldo_inicial' => true,
            'motivo' => MotivoAjuste::AJUSTE_INICIAL,
        ]);
    }

    public function confirmado(): static
    {
        return $this->state(fn (): array => [
            'estado' => AjusteEstado::CONFIRMADO,
            'confirmado_en' => now(),
        ]);
    }
}
