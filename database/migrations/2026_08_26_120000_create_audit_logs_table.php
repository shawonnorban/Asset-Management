<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->dateTime('occurred_at');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name', 100);
            $table->string('action', 50);
            $table->string('table_name', 100);
            $table->unsignedBigInteger('row_id')->nullable();
            $table->text('message')->nullable();
            $table->json('before_data')->nullable();
            $table->json('after_data')->nullable();
            $table->text('url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('http_method', 10)->nullable();
            $table->timestamp('created_at')->nullable();

            // indexes
            $table->index('occurred_at');
            $table->index('user_id');
            $table->index('action');
            $table->index('table_name');
            $table->index(['table_name', 'row_id']);

            // foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('audit_logs');
    }
};
