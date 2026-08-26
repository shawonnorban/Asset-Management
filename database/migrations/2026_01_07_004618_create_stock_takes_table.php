<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_takes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('stock_take_code', 30)->unique();
            $table->string('name', 100);
            $table->date('stock_take_date');
            $table->enum('status', ['DRAFT', 'FINAL'])->default('DRAFT');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            // indexes
            $table->index('user_id');
            $table->index('stock_take_date');

            // foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('stock_takes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('stock_takes');
    }
};
