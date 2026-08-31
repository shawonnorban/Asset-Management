<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')->where('role', 'admin')->update(['role' => 'super_admin']);
        DB::table('roles')->where('role', 'manager')->update(['role' => 'management']);
        DB::table('roles')->where('role', 'staff')->update(['role' => 'employee']);

        if (! DB::table('roles')->where('role', 'department_head')->exists()) {
            DB::table('roles')->insert([
                'role' => 'department_head',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('roles')->where('role', 'super_admin')->update(['role' => 'admin']);
        DB::table('roles')->where('role', 'management')->update(['role' => 'manager']);
        DB::table('roles')->where('role', 'employee')->update(['role' => 'staff']);
        DB::table('roles')->where('role', 'department_head')->delete();
    }
};