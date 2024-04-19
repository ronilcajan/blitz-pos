<?php

use App\Models\ProductCategory;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('barcode')->unique();
            $table->string('sku')->nullable();
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->string('dimension')->nullable();
            $table->string('unit')->nullable();
            $table->string('product_type')->nullable();
            $table->string('brand')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('image')->nullable();
            $table->boolean('isVisible')->default(true);
            $table->timestamp('expiration_date')->nullable();
            $table->string('description')->nullable();
            $table->integer('min_quantity')->nullable();
            $table->decimal('in_store', total: 8, places: 2)->nullable();
            $table->decimal('in_warehouse', total: 8, places: 2)->nullable();
            $table->decimal('price', total: 10, places: 2);
            $table->foreignIdFor(ProductCategory::class)
                ->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Store::class)
                ->constrained()->cascadeOnDelete();
            $table->softDeletes(); // <-- This will add a deleted_at field
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
