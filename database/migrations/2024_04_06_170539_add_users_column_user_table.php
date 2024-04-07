<?php

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
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_photo_url')->after('email')->nullable();
            $table->string('phone')->after('profile_photo_url')->nullable();
            $table->string('address')->after('phone')->nullable();
            $table->foreignIdFor(Store::class)->after('address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'profile_photo_url',
                'phone',
                'address',
                'store_id',
            ]);
        });
    }
};
