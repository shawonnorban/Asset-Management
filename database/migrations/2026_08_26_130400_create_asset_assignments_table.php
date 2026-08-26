<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('asset_id');
            $table->unsignedBigInteger('employee_id');

            // where the asset sat during this assignment
            $table->unsignedBigInteger('location_id')->nullable();

            $table->date('assigned_at');
            $table->date('returned_at')->nullable();

            $table->enum('condition_on_assign', ['NEW', 'GOOD', 'FAIR', 'POOR'])->nullable();
            $table->enum('condition_on_return', ['NEW', 'GOOD', 'FAIR', 'POOR'])->nullable();

            $table->text('note')->nullable();

            // IT staff who handed it over / took it back
            $table->unsignedBigInteger('handled_by')->nullable();

            $table->timestamps();

            $table->index('asset_id');
            $table->index('employee_id');
            $table->index('returned_at');

            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->restrictOnDelete();
            $table->foreign('location_id')->references('id')->on('asset_locations')->nullOnDelete();
            $table->foreign('handled_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->dropForeign(['asset_id']);
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['location_id']);
            $table->dropForeign(['handled_by']);
        });

        Schema::dropIfExists('asset_assignments');
    }
};
