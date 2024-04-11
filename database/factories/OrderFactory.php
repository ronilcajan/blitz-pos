<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_no' => fake()->ean8(),
            'quantity' => 100,
            'amount' => '2000',
            'discount' => 10,
            'supplier_id' => function () {
                return Supplier::inRandomOrder()->first()->id;
            },
            'user_id' => 2,
            'store_id' => function () {
                return Store::inRandomOrder()->first()->id;
            },
        ];
    }
}
