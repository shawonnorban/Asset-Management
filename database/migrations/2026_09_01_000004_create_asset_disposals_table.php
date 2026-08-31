<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_disposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('REQUESTED');
            $table->string('reason', 255)->nullable();
            $table->string('method', 80)->nullable();
            $table->decimal('value_recovered', 12, 2)->default(0);
            $table->date('requested_at');
            $table->date('disposed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('asset_id');
            $table->index('status');
            $table->index('requested_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_disposals');
    }
};
