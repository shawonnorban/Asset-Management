<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('maintenance_type', 80)->nullable();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->string('priority', 30)->default('MEDIUM');
            $table->string('status', 30)->default('OPEN');
            $table->date('requested_at');
            $table->date('scheduled_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->decimal('estimated_cost', 12, 2)->default(0);
            $table->decimal('actual_cost', 12, 2)->default(0);
            $table->string('vendor_name', 150)->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('asset_id');
            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
