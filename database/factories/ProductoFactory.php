<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Impuesto;
use App\Models\Marca;
use App\Models\UnidadMedida;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'codigo' => fake()->unique()->numerify('PROD-#####'),
            'codigo_barras' => fake()->ean13(),
            'nombre' => fake()->words(3, true),
            'nombre_comun' => fake()->optional(0.3)->word(),
            'precio_compra' => fake()->randomFloat(2, 1000, 50000),
            'costo_promedio' => null,
            'precio_venta' => fake()->randomFloat(2, 2000, 100000),
            'stock_minimo' => 10,
            'stock_maximo' => 100,
            'imagen' => null,
            'activo' => true,
            'categoria_id' => Categoria::factory(),
            'marca_id' => Marca::factory(),
            'unidad_medida_id' => UnidadMedida::factory(),
            'impuesto_id' => Impuesto::factory(),
            'usuario_id' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function ($producto) {
            // Si no tiene impuesto_id asignado, buscar uno existente
            if (! $producto->impuesto_id) {
                $impuesto = Impuesto::first();
                if ($impuesto) {
                    $producto->update(['impuesto_id' => $impuesto->id]);
                }
            }
        });
    }
}
