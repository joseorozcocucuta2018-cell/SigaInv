<?php

namespace Database\Factories;

use App\Models\Bodega;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrasladoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'bodega_origen_id' => Bodega::factory(),
            'bodega_destino_id' => Bodega::factory(),
            'estado' => 'borrador',
            'confirmada_en' => null,
            'revertida_en' => null,
            'fecha' => fake()->dateTimeBetween('-1 year', 'now'),
            'observaciones' => null,
            'usuario_id' => User::factory(),
        ];
    }

    public function confirmada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'confirmada',
            'confirmada_en' => now(),
        ]);
    }

    public function revertida(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'revertida',
            'revertida_en' => now(),
        ]);
    }
}
