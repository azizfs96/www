<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waf:create-admin 
                            {--email=admin@waf.local : Admin email address}
                            {--password=admin123 : Admin password}
                            {--name=Super Admin : Admin name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or update super admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');
        $name = $this->option('name');

        // Check if admin already exists
        $admin = User::where('email', $email)->first();

        if ($admin) {
            // Update existing admin
            $admin->update([
                'name' => $name,
                'password' => Hash::make($password),
                'role' => 'super_admin',
            ]);

            $this->info("✅ Super Admin updated successfully!");
            $this->info("   Email: {$email}");
            $this->info("   Password: {$password}");
            $this->info("   Role: super_admin");
        } else {
            // Create new admin
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'super_admin',
            ]);

            $this->info("✅ Super Admin created successfully!");
            $this->info("   Email: {$email}");
            $this->info("   Password: {$password}");
            $this->info("   Role: super_admin");
        }

        return Command::SUCCESS;
    }
}
