<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // make sure the 'admin' role exists
        $role = DB::table('roles')->where('role', 'admin')->first();

        if (!$role) {
            $this->command->error("Role 'admin' was not found. Run RoleSeeder first.");
            return;
        }

        // create the admin user (adjust as needed)
        $userId = DB::table('users')->insertGetId([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'), // change if needed
            'role_id' => $role->id,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->command->info("Admin user created with id: {$userId} (email: admin@example.com / password: password)");
    }
}
