<?php

namespace Database\Factories;

use App\Enums\VentaEstado;
use App\Models\Bodega;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VentaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'numero' => 'VEN-'.fake()->unique()->numerify('######'),
            'estado' => VentaEstado::BORRADOR,
            'confirmada_en' => null,
            'cliente_id' => Cliente::factory(),
            'bodega_id' => Bodega::factory(),
            'usuario_id' => User::factory(),
            'cotizacion_id' => null,
            'remision_id' => null,
            'fecha' => fake()->dateTimeBetween('-1 year', 'now'),
            'subtotal' => 0,
            'descuento' => 0,
            'impuestos' => 0,
            'total' => 0,
            'saldo_pendiente' => 0,
            'estado_pago' => 'pendiente',
            'fecha_vencimiento' => null,
            'observaciones' => null,
            'total_confirmado' => null,
            'impuestos_confirmados' => null,
            'snapshot_confirmacion' => null,
        ];
    }

    public function confirmada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => VentaEstado::CONFIRMADA,
            'confirmada_en' => now(),
        ]);
    }

    public function anulada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => VentaEstado::ANULADA,
        ]);
    }

    public function withTotal(float $total): static
    {
        return $this->state(fn (array $attributes) => [
            'subtotal' => $total,
            'impuestos' => $total * 0.19,
            'total' => $total * 1.19,
            'saldo_pendiente' => $total * 1.19,
        ]);
    }
}
