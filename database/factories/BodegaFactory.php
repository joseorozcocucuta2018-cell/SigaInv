<?php

namespace Database\Factories;

use App\Enums\BodegaEstado;
use App\Models\Ciudad;
use App\Models\Departamento;
use Illuminate\Database\Eloquent\Factories\Factory;

class BodegaFactory extends Factory
{
    public function definition(): array
    {
        $departamento = Departamento::factory()->create();
        $ciudad = Ciudad::factory()->create(['departamento_id' => $departamento->id]);

        return [
            'nombre' => fake()->company().' - '.fake()->randomElement(['Principal', 'Secundaria', 'Norte', 'Sur', 'Centro']),
            'descripcion' => fake()->sentence(),
            'direccion1' => fake()->streetAddress(),
            'direccion2' => null,
            'departamento_id' => $departamento->id,
            'ciudad_id' => $ciudad->id,
            'estado' => BodegaEstado::ACTIVO,
            'usuario_id' => null,
            'es_principal' => false,
        ];
    }

    public function principal(): static
    {
        return $this->state(fn (array $attributes) => [
            'es_principal' => true,
        ]);
    }
}
