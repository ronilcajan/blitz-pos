<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Expenses;
use App\Models\ExpensesCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\ProductSupplier;
use App\Models\ProductUnit;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call(UserSeeder::class);
        Store::factory(100)->create();
        Customer::factory(100)->create();
        Supplier::factory(100)->create();
        ExpensesCategory::factory(20)->create();
        Expenses::factory(100)->create();
        ProductUnit::factory(20)->create();
        ProductCategory::factory(20)->create();
        Product::factory(100)->create();
        ProductPrice::factory(100)->create();
        ProductStock::factory(100)->create();
    }
}
