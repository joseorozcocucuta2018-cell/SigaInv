<?php

namespace Database\Factories;

use App\Models\Departamento;
use Illuminate\Database\Eloquent\Factories\Factory;

class CiudadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => fake()->city(),
            'departamento_id' => Departamento::factory(),
        ];
    }
}
