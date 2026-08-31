<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_audits', function (Blueprint $table) {
            $table->id();
            $table->string('audit_name');
            $table->date('audit_period'); // Month/Year of audit
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->unsignedBigInteger('started_by')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->integer('total_assignments')->default(0);
            $table->integer('verified_count')->default(0);
            $table->integer('missing_count')->default(0);
            $table->integer('damaged_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('started_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('completed_by')->references('id')->on('users')->nullOnDelete();
            $table->index('audit_period');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_audits');
    }
};
