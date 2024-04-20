<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductStock>
 */
class ProductStockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => 'KS '.fake()->ean8(),
            'min_quantity' => fake()->numberBetween(10, 50),
            'in_warehouse' => fake()->numberBetween(1000, 10000),
            'in_store' => fake()->numberBetween(1000, 10000),
            'product_id' => function () {
                return Product::inRandomOrder()->first()->id;
            },
        ];
    }
}
