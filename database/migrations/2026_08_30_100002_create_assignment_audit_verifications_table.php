<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_audit_verifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('audit_id');
            $table->unsignedBigInteger('assignment_id');
            $table->unsignedBigInteger('asset_id');
            $table->unsignedBigInteger('employee_id');
            $table->enum('verification_status', ['pending', 'confirmed', 'missing', 'lost', 'damaged', 'returned', 'transferred'])->default('pending');
            $table->string('condition_observed')->nullable(); // good, fair, poor, damaged
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('audit_id')->references('id')->on('assignment_audits')->cascadeOnDelete();
            $table->foreign('assignment_id')->references('id')->on('asset_assignments')->cascadeOnDelete();
            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['audit_id', 'verification_status'], 'audit_status_idx');
            $table->index(['employee_id', 'verification_status'], 'employee_status_idx');
            $table->unique(['audit_id', 'assignment_id'], 'audit_assignment_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_audit_verifications');
    }
};
