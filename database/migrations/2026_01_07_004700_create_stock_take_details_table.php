<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_take_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('stock_take_id');
            $table->unsignedBigInteger('asset_id');
            $table->enum('physical_status', ['PRESENT', 'NOT_FOUND', 'DAMAGED', 'LOST']);
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('note', 500)->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            // one asset can only be recorded once per stock take
            $table->unique(['stock_take_id', 'asset_id']);

            // indexes
            $table->index('asset_id');
            $table->index('location_id');
            $table->index('employee_id');
            $table->index('user_id');

            // foreign keys
            $table->foreign('stock_take_id')->references('id')->on('stock_takes')->onDelete('cascade');
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('asset_locations')->nullOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('stock_take_details', function (Blueprint $table) {
            $table->dropForeign(['stock_take_id']);
            $table->dropForeign(['asset_id']);
            $table->dropForeign(['location_id']);
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('stock_take_details');
    }
};
