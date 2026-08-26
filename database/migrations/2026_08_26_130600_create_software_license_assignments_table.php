<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('software_license_assignments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('software_license_id');
            $table->unsignedBigInteger('asset_id');

            $table->date('installed_at');
            $table->date('removed_at')->nullable();
            $table->text('note')->nullable();

            $table->unsignedBigInteger('handled_by')->nullable();

            $table->timestamps();

            // counting the seats in use goes through this pair
            $table->index(['software_license_id', 'removed_at'], 'sla_license_removed_index');
            $table->index('asset_id', 'sla_asset_index');

            $table->foreign('software_license_id')->references('id')->on('software_licenses')->cascadeOnDelete();
            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();
            $table->foreign('handled_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('software_license_assignments', function (Blueprint $table) {
            $table->dropForeign(['software_license_id']);
            $table->dropForeign(['asset_id']);
            $table->dropForeign(['handled_by']);
        });

        Schema::dropIfExists('software_license_assignments');
    }
};
