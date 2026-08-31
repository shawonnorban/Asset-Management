<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('title', 150);
            $table->string('maintenance_type', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('vendor', 120)->nullable();
            $table->date('scheduled_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->decimal('cost', 12, 2)->default(0);
            $table->string('status', 30)->default('SCHEDULED');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_records');
    }
};