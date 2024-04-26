<?php

use App\Models\Store;
use App\Models\Supplier;
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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('tx_no');
            $table->decimal('quantity',8,2);
            $table->integer('discount');
            $table->decimal('amount',8,2);
            $table->decimal('total',8,2);
            $table->string('status',20)->default('pending');
            $table->string('notes')->nullable();
            $table->foreignIdFor(Supplier::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Store::class)->constrained()->cascadeOnDelete();
            $table->softDeletes(); // <-- This will add a deleted_at field
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
