<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $store = Store::create([
            'name' => 'Store',
        ]);
        
        $user = User::create([
            'name' => 'Super Admin User',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('superadmin@example.com'),
            'email_verified_at' => now(),
        ]);
        $user2 = User::create([
            'name' => 'Owner User',
            'email' => 'owner@example.com',
            'password' => Hash::make('owner@example.com'),
            'store_id' =>  $store->id,
            'email_verified_at' => now(),
        ]);

        $user3 = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin@example.com'),
            'store_id' =>  $store->id,
            'email_verified_at' => now(),
        ]);

        $user4 = User::create([
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'password' => Hash::make('staff@example.com'),
            'store_id' =>  $store->id,
            'email_verified_at' => now(),
        ]);

        $user->addRole(1); // parameter can be a Role object, BackedEnum, array, id or the role string name
        $user2->addRole(2);
        $user3->addRole(3);
        $user4->addRole(4);
    }
}
