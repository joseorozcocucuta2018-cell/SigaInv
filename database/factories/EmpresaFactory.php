<?php

namespace Database\Factories;

use App\Enums\EmpresaRegimenTributario;
use App\Enums\EmpresaTipoPersona;
use App\Models\Ciudad;
use App\Models\Departamento;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmpresaFactory extends Factory
{
    protected $table = 'empresa';

    public function definition(): array
    {
        $departamento = Departamento::factory()->create();
        $ciudad = Ciudad::factory()->create(['departamento_id' => $departamento->id]);
        $nit = fake()->unique()->numerify('##########');

        return [
            'razon_social' => fake()->company(),
            'nombre_comercial' => fake()->company(),
            'nit' => $nit,
            'digito_verificacion' => fake()->numberBetween(0, 9),
            'tipo_persona' => EmpresaTipoPersona::JURIDICA,
            'regimen_tributario' => EmpresaRegimenTributario::COMUN,
            'responsable_iva' => true,
            'usa_seriales' => false,
            'una_sola_bodega' => false,
            'actividad_ciiu' => '4711',
            'direccion' => fake()->streetAddress(),
            'departamento_id' => $departamento->id,
            'ciudad_id' => $ciudad->id,
            'pais' => 'Colombia',
            'telefono' => fake()->numerify('3#########'),
            'email' => fake()->unique()->safeEmail(),
            'margen_ganancia_default' => 30.00,
            'margen_ganancia_minimo' => 25.00,
        ];
    }

    public function simplificado(): static
    {
        return $this->state(fn (): array => [
            'regimen_tributario' => EmpresaRegimenTributario::SIMPLIFICADO,
            'responsable_iva' => false,
        ]);
    }
}
