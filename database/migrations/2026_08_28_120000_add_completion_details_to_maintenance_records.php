<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_records', function (Blueprint $table) {
            $table->decimal('final_cost', 12, 2)->nullable()->after('cost');
            $table->text('completion_remarks')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_records', function (Blueprint $table) {
            $table->dropColumn(['final_cost', 'completion_remarks']);
        });
    }
};