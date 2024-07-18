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
            'base_price' => fake()->numberBetween(500, 1000),
            'markup_price' => fake()->numberBetween(100, 200),
            'discount_rate' => fake()->numberBetween(5, 20),
            'discount_type' => fake()->randomElement(['none', 'flat', 'percentage']),
            'tax_rate' => fake()->numberBetween(5, 20),
            'tax_type' => fake()->randomElement(['none', 'flat', 'percentage']),
            'sale_price' => fake()->numberBetween(500, 1000),
            'product_id' => function () {
                return Product::inRandomOrder()->first()->id;
            },
        ];
    }
}
