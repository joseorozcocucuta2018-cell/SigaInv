<?php

namespace Database\Factories;

use App\Models\Compra;
use App\Models\Impuesto;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetalleCompraFactory extends Factory
{
    public function definition(): array
    {
        return [
            'compra_id' => Compra::factory(),
            'producto_id' => Producto::factory(),
            'cantidad' => fake()->numberBetween(1, 100),
            'precio_unitario' => fake()->randomFloat(2, 100, 50000),
            'descuento_unitario' => 0,
            'impuesto_id' => Impuesto::factory(),
            'subtotal' => 0,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function ($detalle) {
            $subtotal = $detalle->cantidad * $detalle->precio_unitario;
            $detalle->update(['subtotal' => $subtotal]);
        });
    }
}
