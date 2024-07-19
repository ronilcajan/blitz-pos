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
            $table->string('barcode')->nullable();
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->string('dimension')->nullable();
            $table->string('unit')->nullable();
            $table->string('product_type')->default('sellable');
            $table->string('brand')->nullable();
            $table->string('image')->nullable();
            $table->string('visible')->default('published');
            $table->timestamp('expiration_date')->nullable();
            $table->text('description')->nullable();
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
