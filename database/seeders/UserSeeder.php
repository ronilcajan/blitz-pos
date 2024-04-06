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
        /**
     * Run the database seeds.
     */
        $superadmin = Role::create([
            'name' => 'super-admin',
            'display_name' => 'System owner', // optional
            'description' => 'User is the superadmin of a the system', // optional
        ]);

        $owner = Role::create([
            'name' => 'owner',
            'display_name' => 'Tenant Owner', // optional
            'description' => 'User is the owner of a given project', // optional
        ]);
        
        $admin = Role::create([
            'name' => 'admin',
            'display_name' => 'Tenant Administrator', // optional
            'description' => 'User is allowed to manage,edit and delete other users', // optional
        ]);

        $staff = Role::create([
            'name' => 'staff',
            'display_name' => 'Tenant Staff', // optional
            'description' => 'User is allowed to manage and edit other users', // optional
        ]);
        
        $tenant = Store::create([
            'name' => 'Store',
        ]);
        
        $user = User::create([
            'name' => 'Super Admin User',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('superadmin@example.com'),
        ]);

        $user2 = User::create([
            'name' => 'Owner User',
            'email' => 'owner@example.com',
            'password' => Hash::make('owner@example.com'),
            'store_id' =>  $tenant->id
        ]);

        $user3 = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin@example.com'),
            'store_id' =>  $tenant->id
        ]);

        $user4 = User::create([
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'password' => Hash::make('staff@example.com'),
            'store_id' =>  $tenant->id
        ]);

        $user->addRole($superadmin); // parameter can be a Role object, BackedEnum, array, id or the role string name
        $user2->addRole($owner);
        $user3->addRole($admin);
        $user4->addRole($staff);
    }
}
