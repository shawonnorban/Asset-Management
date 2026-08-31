<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warranties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('vendor_name', 150)->nullable();
            $table->string('warranty_type', 80)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 30)->default('ACTIVE');
            $table->text('coverage_details')->nullable();
            $table->string('claim_status', 30)->default('NOT_STARTED');
            $table->timestamps();

            $table->index('asset_id');
            $table->index('status');
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warranties');
    }
};
