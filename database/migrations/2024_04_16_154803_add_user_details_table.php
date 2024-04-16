<?php

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
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->string('birthdate')->nullable();
            $table->string('bio')->nullable();
            $table->string('education')->nullable();
            $table->string('position')->nullable();
            $table->string('join_date')->nullable();
            $table->string('hobbies')->nullable();
            $table->string('skills')->nullable();
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
        Schema::dropIfExists('user_details');
    }
};
