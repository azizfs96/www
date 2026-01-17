<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create super admin if not exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@waf.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('admin123'),
                'role' => 'super_admin',
            ]
        );

        if ($admin->wasRecentlyCreated) {
            $this->command->info('Super Admin created: admin@waf.local / admin123');
        } else {
            $this->command->info('Super Admin already exists');
        }
    }
}
