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
            'barcode' => fake()->ean13(),
            'size' => '1x2',
            'color' => fake()->colorName(),
            'brand' => fake()->company(),
            'unit'=> function () {
                return ProductUnit::inRandomOrder()->first()->name;
            },
            'description' => fake()->text(),
            'product_category_id' => function () {
                return ProductCategory::inRandomOrder()->first()->id;
            },
            'store_id' => function () {
                return Store::inRandomOrder()->first()->id;
            },
        ];
    }
}
