<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'sku' => strtoupper(fake()->unique()->bothify('???-####')),
            'price' => fake()->randomFloat(2, 5, 500),
            'stock_quantity' => fake()->numberBetween(0, 100),
            'category' => fake()->randomElement(['Kitchen', 'Textiles', 'Ceramics', 'Home', 'Garden']),
        ];
    }

    public function stock(int $quantity): static
    {
        return $this->state(fn () => ['stock_quantity' => $quantity]);
    }

    public function price(float $price): static
    {
        return $this->state(fn () => ['price' => $price]);
    }
}
