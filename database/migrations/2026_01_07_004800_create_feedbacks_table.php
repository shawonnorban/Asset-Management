<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('issue_report_id');
            $table->unsignedBigInteger('asset_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('decision_analysis', 255)->nullable();
            $table->string('status', 30)->nullable();
            $table->timestamps();

            // indexes
            $table->index('issue_report_id');
            $table->index('asset_id');
            $table->index('user_id');

            // foreign keys
            $table->foreign('issue_report_id')->references('id')->on('issue_reports')->onDelete('cascade');
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            $table->dropForeign(['issue_report_id']);
            $table->dropForeign(['asset_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('feedbacks');
    }
};
