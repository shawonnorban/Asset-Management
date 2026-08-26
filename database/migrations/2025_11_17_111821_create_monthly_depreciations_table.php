<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_depreciations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('asset_id');
            $table->date('period'); // store as YYYY-MM-01
            $table->enum('method', ['STRAIGHT_LINE', 'DECLINING_BALANCE']);
            $table->decimal('monthly_expense', 18, 2);
            $table->decimal('accumulated_depreciation', 18, 2);
            $table->decimal('ending_book_value', 18, 2);
            $table->unsignedBigInteger('user_id');
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();

            // indexes
            $table->unique(['asset_id', 'period']);
            $table->index('asset_id');
            $table->index('period');
            $table->index(['user_id', 'period']);

            // foreign keys
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('monthly_depreciations', function (Blueprint $table) {
            $table->dropForeign(['asset_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::dropIfExists('monthly_depreciations');
    }
};
