<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ProductSupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'unit_price' => 100,
            'mark_up_price' => 50,
            'product_id' => function () {
                return Product::inRandomOrder()->first()->id;
            },
            'supplier_id' => function () {
                return Supplier::inRandomOrder()->first()->id;
            },
        ];
    }
}
