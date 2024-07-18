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
            $table->dropColumn('flat_percentage');
            $table->dropColumn('discount_price');
            $table->dropColumn('discount');

            $table->decimal('discount_rate',10,2)
                ->after('markup_price')
                ->default(0);

            $table->enum('discount_type',['flat','percentage','none'])
                ->after('discount_rate')
                ->default('none');

            $table->decimal('tax_rate',10,2)->after('discount_type')
                ->default(0);

            $table->enum('tax_type',['flat','percentage','none'])
                ->after('tax_rate')
                ->default('none');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_price', function (Blueprint $table) {
            $table->string('flat_percentage');
            $table->decimal('discount_price');
            $table->decimal('discount');

            $table->dropColumn('discount',10,2)->after('markup_price')
                ->default(0);
            $table->dropColumn('discount_type',['flat','percentage','none'])
                ->after('discount')->default('none');
            $table->dropColumn('tax',10,2)->after('markup_price')
                ->default(0);
            $table->dropColumn('tax_type',['flat','percentage','none'])
                ->after('tax')->default('none');
        });
    }
};
