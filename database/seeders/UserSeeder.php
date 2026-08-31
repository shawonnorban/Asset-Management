<?php

namespace Database\Seeders;

use App\Models\User;
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

        // make sure the protected role exists
        $role = DB::table('roles')->where('name', 'super_admin')->first()
            ?? DB::table('roles')->where('role', 'super_admin')->first();

        if (!$role) {
            $this->command->error("Role 'super_admin' was not found. Run RoleSeeder first.");
            return;
        }

        // create the admin user (adjust as needed)
        $existing = DB::table('users')->where('email', 'admin@example.com')->first();

        if ($existing) {
            $userId = $existing->id;
            DB::table('users')->where('id', $userId)->update(['role_id' => $role->id, 'updated_at' => $now]);
        } else {
            $userId = DB::table('users')->insertGetId([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'), // change if needed
                'role_id' => $role->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // The route guards are Spatie's permission middleware, which reads the
        // spatie role tables - not the legacy role_id column. Without this the
        // seeded admin is locked out of every guarded page.
        User::find($userId)?->syncRoles(['super_admin']);

        $this->command->info("Admin user created with id: {$userId} (email: admin@example.com / password: password)");
    }
}
