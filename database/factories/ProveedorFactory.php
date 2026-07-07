<?php

namespace Database\Factories;

use App\Enums\ProveedorEstado;
use App\Models\Ciudad;
use App\Models\Departamento;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProveedorFactory extends Factory
{
    public function definition(): array
    {
        $departamento = Departamento::factory()->create();
        $ciudad = Ciudad::factory()->create(['departamento_id' => $departamento->id]);

        return [
            'nombre' => fake()->company(),
            'documento' => fake()->unique()->numerify('##########'),
            'tipo_documento' => 'NIT',
            'telefono' => fake()->numerify('3#########'),
            'email' => fake()->unique()->safeEmail(),
            'direccion1' => fake()->streetAddress(),
            'direccion2' => null,
            'departamento_id' => $departamento->id,
            'ciudad_id' => $ciudad->id,
            'saldo' => 0,
            'pais' => 'Colombia',
            'estado' => ProveedorEstado::ACTIVO,
            'limite_credito' => 0,
            'dias_credito' => 0,
            'dias_pago' => 0,
            'contacto_principal' => null,
            'sitio_web' => null,
            'usuario_id' => null,
        ];
    }
}
