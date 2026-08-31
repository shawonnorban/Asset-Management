<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->unsignedBigInteger('from_location_id')->nullable();
            $table->unsignedBigInteger('to_location_id')->nullable();
            $table->unsignedBigInteger('from_employee_id')->nullable();
            $table->unsignedBigInteger('to_employee_id')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('REQUESTED');
            $table->string('reason', 255)->nullable();
            $table->text('notes')->nullable();
            $table->date('requested_at');
            $table->date('transferred_at')->nullable();
            $table->timestamps();

            $table->index('asset_id');
            $table->index('status');
            $table->index('requested_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_transfers');
    }
};
