<?php

namespace Database\Factories;

use App\Models\ExpensesCategory;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expenses>
 */
class ExpensesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'expenses_date' => fake()->date(),
            'vendor' => fake()->company(),
            'amount' => '2000',
            'notes' => fake()->text(),
            'attachments' => fake()->imageUrl(),
            'expenses_category_id' => function () {
                return ExpensesCategory::inRandomOrder()->first()->id;
            },
            'user_id' => function () {
                return User::inRandomOrder()->first()->id;
            },
            'store_id' => function () {
                return Store::inRandomOrder()->first()->id;
            },
        ];
    }
}
