<?php

namespace Database\Factories;

use App\Enums\AuditoriaOperacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditoriaFactory extends Factory
{
    protected $table = 'auditorias';

    public function definition(): array
    {
        return [
            'usuario_id' => User::factory(),
            'tabla' => fake()->randomElement(['productos', 'clientes', 'ventas']),
            'operacion' => AuditoriaOperacion::INSERT,
            'registro_id' => fake()->numberBetween(1, 1000),
            'datos_anteriores' => null,
            'datos_nuevos' => ['campo' => 'valor'],
            'fecha_operacion' => now(),
        ];
    }

    public function update(): static
    {
        return $this->state(fn (): array => [
            'operacion' => AuditoriaOperacion::UPDATE,
            'datos_anteriores' => ['campo' => 'anterior'],
            'datos_nuevos' => ['campo' => 'nuevo'],
        ]);
    }

    public function delete(): static
    {
        return $this->state(fn (): array => [
            'operacion' => AuditoriaOperacion::DELETE,
            'datos_nuevos' => null,
        ]);
    }
}
