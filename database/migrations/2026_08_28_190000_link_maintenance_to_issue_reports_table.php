<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_records', function (Blueprint $table) {
            $table->foreignId('issue_report_id')->nullable()->after('asset_id')->constrained('issue_reports')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_records', function (Blueprint $table) {
            $table->dropForeign(['issue_report_id']);
            $table->dropColumn('issue_report_id');
        });
    }
};
