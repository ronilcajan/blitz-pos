<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name,
            'bracode' => fake()->ean13(),
            'sku' => 'KS '.fake()->ean13(),
            'unit'=> function () {
                return ProductUnit::inRandomOrder()->first()->name;
            },
            'product_type' => 'sellable',
            'brand' => fake()->company(),
            'manufacturer' => fake()->company(),
            'description' => fake()->text(),
            'image' => fake()->imageUrl(),
            'product_category_id' => function () {
                return ProductCategory::inRandomOrder()->first()->id;
            },
            'store_id' => function () {
                return Store::inRandomOrder()->first()->id;
            },
        ];
    }
}
