<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductPrice>
 */
class ProductPriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'base_price' => fake()->numberBetween(100, 1000),
            'markup_price' => fake()->numberBetween(10, 50),
            'sale_price' => fake()->numberBetween(1000, 10000),
            'discount' => fake()->numberBetween(10, 50),
            'discount_price' => fake()->numberBetween(1000, 10000),
            'product_id' => function () {
                return Product::inRandomOrder()->first()->id;
            },
        ];
    }
}
