<?php

use App\Models\Customer;
use App\Models\Store;
use App\Models\User;
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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('tx_no');
            $table->decimal('quantity',10,2);
            $table->decimal('sub_total',10,2)->default(0);
            $table->decimal('discount',10,2)->default(0);
            $table->decimal('tax',10,2)->default(0);
            $table->decimal('total',10,2);
            $table->decimal('payment_tender',10,2);
            $table->decimal('payment_changed',10,2)->default(0);
            $table->string('payment_method')->default('cash');
            $table->string('referrence')->nullable();
            $table->string('notes')->nullable();
            $table->string('status')->default('complete');
            $table->foreignIdFor(Customer::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Store::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->softDeletes(); // <-- This will add a deleted_at field
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
