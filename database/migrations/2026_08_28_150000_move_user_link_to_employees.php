<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()->after('image')->constrained('users')->nullOnDelete();
        });

        DB::table('users')->whereNotNull('employee_id')->get(['id', 'employee_id'])->each(function ($user) {
            DB::table('employees')->where('id', $user->employee_id)->update(['user_id' => $user->id]);
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};