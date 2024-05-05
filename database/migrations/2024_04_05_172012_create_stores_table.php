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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('founder')->nullable();
            $table->string('tagline')->nullable();
            $table->string('email')->nullable();
            $table->string('contact')->nullable();
            $table->string('website')->nullable();
            $table->string('industry')->nullable();
            $table->string('country')->nullable();
            $table->string('country_code')->nullable();
            $table->string('timezone')->nullable();
            $table->string('currency')->nullable();
            $table->string('flag')->nullable();
            $table->string('tax')->nullable();
            $table->string('description')->nullable();
            $table->string('address')->nullable();
            $table->string('avatar')->nullable();
            $table->softDeletes(); // <-- This will add a deleted_at field
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
