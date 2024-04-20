<?php

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_price', function (Blueprint $table) {
            $table->id();
            $table->decimal('base_price', total: 10, places: 2);
            $table->decimal('markup_price', total: 10, places: 2);
            $table->decimal('sale_price', total: 10, places: 2);
            $table->decimal('discount', total: 10, places: 2);
            $table->string('manual_percentage')->default('manual');
            $table->decimal('discount_price', total: 10, places: 2);
            $table->foreignIdFor(Product::class)->constrained()->cascadeOnDelete();
            $table->softDeletes(); // <-- This will add a deleted_at field
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_price');
    }
};
