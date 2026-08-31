<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('type', 80);
            $table->string('title', 150);
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('is_read');
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
