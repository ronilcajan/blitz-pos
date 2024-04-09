<?php

use App\Models\Product;
use App\Models\Supplier;
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
        Schema::create('product_supplier', function (Blueprint $table) {
            $table->id();
            $table->decimal('unit_price', total: 8, places: 2)->nullable();
            $table->decimal('mark_up_price', total: 8, places: 2)->nullable();
            $table->decimal('retail_price', total: 8, places: 2)->nullable();
            $table->string('manual_percentage')->default('manual');
            $table->integer('min_quantity')->nullable();
            $table->decimal('in_store', total: 8, places: 2)->nullable();
            $table->decimal('in_warehouse', total: 8, places: 2)->nullable();
            $table->foreignIdFor(Product::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Supplier::class)->constrained()->cascadeOnDelete();
            $table->softDeletes(); // <-- This will add a deleted_at field
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_supplier');
    }
};
