<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ImpuestoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => 'IVA '.fake()->randomElement([19, 16, 12, 5, 0]).'%',
            'codigo' => 'IVA'.fake()->unique()->numberBetween(1, 99),
            'porcentaje' => fake()->randomElement([19, 16, 12, 5, 0]),
            'descripcion' => fake()->sentence(),
            'activo' => true,
        ];
    }

    public function activo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => true,
        ]);
    }

    public function inactivo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => false,
        ]);
    }
}
