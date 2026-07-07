<?php

namespace Database\Factories;

use App\Models\Bodega;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockBodegaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'producto_id' => Producto::factory(),
            'bodega_id' => Bodega::factory(),
            'cantidad' => fake()->numberBetween(0, 100),
        ];
    }

    public function withQuantity(int $quantity): static
    {
        return $this->state(fn (array $attributes) => [
            'cantidad' => $quantity,
        ]);
    }
}
