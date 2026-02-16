<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@chekuleft.com'],
            [
                'name' => 'Super Admin',
                'email' => 'admin@chekuleft.com',
                'username' => 'superadmin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
                'butcher_id' => null,
            ]
        );
    }
}
