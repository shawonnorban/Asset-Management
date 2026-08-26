<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_depreciation_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('asset_id')->unique();
            $table->integer('tax_depreciation_group_id');
            $table->enum('method', ['STRAIGHT_LINE', 'DECLINING_BALANCE'])->default('STRAIGHT_LINE');
            $table->decimal('acquisition_cost', 18, 2)->default(0);
            $table->decimal('salvage_value', 18, 2)->nullable();
            $table->integer('useful_life_months')->nullable();
            $table->boolean('is_disposed')->default(false);
            $table->date('in_service_date');
            $table->enum('disposal_reason', ['DAMAGED', 'SOLD', 'DONATED', 'LOST', 'OTHER'])->nullable();
            $table->text('disposal_note')->nullable();
            $table->timestamps();

            // indexes
            $table->index('tax_depreciation_group_id');
            $table->index('in_service_date');

            // foreign keys
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
            $table->foreign('tax_depreciation_group_id')->references('id')->on('tax_depreciation_groups')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('asset_depreciation_settings', function (Blueprint $table) {
            $table->dropForeign(['asset_id']);
            $table->dropForeign(['tax_depreciation_group_id']);
        });

        Schema::dropIfExists('asset_depreciation_settings');
    }
};
