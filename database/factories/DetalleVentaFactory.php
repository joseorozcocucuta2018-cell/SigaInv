<?php

namespace Database\Factories;

use App\Models\Impuesto;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetalleVentaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'venta_id' => Venta::factory(),
            'producto_id' => Producto::factory(),
            'cantidad' => fake()->numberBetween(1, 10),
            'precio_unitario' => fake()->randomFloat(2, 1000, 50000),
            'descuento_unitario' => 0,
            'impuesto_id' => Impuesto::factory(),
            'subtotal' => 0,
            'costo_unitario' => null,
            'lote' => null,
            'fecha_vencimiento' => null,
            'serial' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function ($detalle) {
            // Calcular subtotal
            $subtotal = $detalle->cantidad * $detalle->precio_unitario;
            $detalle->update(['subtotal' => $subtotal]);
        });
    }

    public function withCosto(float $costo): static
    {
        return $this->state(fn (array $attributes) => [
            'costo_unitario' => $costo,
        ]);
    }
}
