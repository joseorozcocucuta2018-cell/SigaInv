<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DepartamentoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->state().' '.fake()->randomNumber(3),
        ];
    }
}
