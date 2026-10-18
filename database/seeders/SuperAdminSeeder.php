<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $superRole = Role::where('slug', 'superadmin')->first();

        $user = User::firstOrCreate(
            ['email' => 'superadmin@urlshortener.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password123'),
                'status' => 1,
            ]
        );

        if ($superRole) {
            UserRole::firstOrCreate([
                'user_id' => $user->id,
                'role_id' => $superRole->id,
            ]);
        }
    }
}
