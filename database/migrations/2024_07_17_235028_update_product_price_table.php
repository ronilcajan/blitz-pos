<?php

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
        Schema::table('product_price', function (Blueprint $table) {
            $table->dropColumn('manual_percentage');
            $table->string('flat_percentage')->after('discount')->default('flat');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_price', function (Blueprint $table) {
            $table->string('manual_percentage');
            $table->dropColumn('flat_percentage');
        });
    }
};
