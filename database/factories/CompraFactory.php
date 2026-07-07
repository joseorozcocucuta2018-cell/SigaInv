<?php

namespace Database\Factories;

use App\Enums\CompraEstado;
use App\Models\Bodega;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompraFactory extends Factory
{
    public function definition(): array
    {
        return [
            'numero' => 'CMP-'.fake()->unique()->numerify('######'),
            'estado' => CompraEstado::BORRADOR,
            'confirmada_en' => null,
            'proveedor_id' => Proveedor::factory(),
            'bodega_id' => Bodega::factory(),
            'usuario_id' => User::factory(),
            'fecha' => fake()->dateTimeBetween('-1 year', 'now'),
            'subtotal' => 0,
            'descuento' => 0,
            'impuestos' => 0,
            'total' => 0,
            'saldo_pendiente' => 0,
            'fecha_vencimiento' => null,
            'observaciones' => null,
            'total_confirmado' => null,
            'impuestos_confirmados' => null,
            'snapshot_confirmacion' => null,
        ];
    }

    public function registrada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => CompraEstado::REGISTRADA,
            'confirmada_en' => now(),
        ]);
    }

    public function pendiente(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => CompraEstado::PENDIENTE,
            'confirmada_en' => now(),
            'saldo_pendiente' => $attributes['total'] ?? 10000,
        ]);
    }

    public function pagada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => CompraEstado::PAGADA,
            'confirmada_en' => now(),
            'saldo_pendiente' => 0,
        ]);
    }

    public function anulada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => CompraEstado::ANULADA,
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
