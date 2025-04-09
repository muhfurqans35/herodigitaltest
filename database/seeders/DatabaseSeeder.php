<?php

namespace Database\Seeders;


use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        Role::insert([
            ['name' => 'admin'],
            ['name' => 'superadmin'],
            ['name' => 'customer'],
        ]);

        $user = User::create([
            'name' => 'superadmin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('Super@dm1n'),
        ]);

        $superadminRole = Role::where('name', 'superadmin')->first();
        $user->roles()->attach($superadminRole);
    }
}
