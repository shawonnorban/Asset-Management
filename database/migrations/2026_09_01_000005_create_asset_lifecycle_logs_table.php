<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_lifecycle_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 80);
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('event_at')->useCurrent();
            $table->timestamps();

            $table->index('asset_id');
            $table->index('event_type');
            $table->index('event_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_lifecycle_logs');
    }
};
