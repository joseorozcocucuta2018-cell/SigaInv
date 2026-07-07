<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UnidadMedidaFactory extends Factory
{
    public function definition(): array
    {
        static $unidades = [
            ['nombre' => 'Unidad',     'simbolo' => 'UND'],
            ['nombre' => 'Kilogramo',  'simbolo' => 'KG'],
            ['nombre' => 'Litro',      'simbolo' => 'LT'],
            ['nombre' => 'Metro',      'simbolo' => 'MT'],
            ['nombre' => 'Caja',       'simbolo' => 'CJA'],
            ['nombre' => 'Docena',     'simbolo' => 'DOC'],
            ['nombre' => 'Par',        'simbolo' => 'PAR'],
            ['nombre' => 'Gramo',      'simbolo' => 'GR'],
        ];

        $u = fake()->unique()->randomElement($unidades);

        return [
            'nombre' => $u['nombre'],
            'simbolo' => $u['simbolo'],
            'descripcion' => null,
            'activo' => true,
        ];
    }
}
