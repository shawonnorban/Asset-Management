<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('asset_code', 35)->unique();
            $table->string('image', 255)->nullable();
            $table->string('asset_name', 150);
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('serial_number', 100)->nullable()->unique();
            $table->text('description')->nullable();
            $table->date('added_date');

            // procurement
            $table->string('vendor', 100)->nullable();
            $table->string('invoice_no', 60)->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 18, 2)->nullable();
            $table->date('warranty_start')->nullable();
            $table->date('warranty_end')->nullable();

            // lifecycle
            $table->enum('status', [
                'IN_USE',
                'IN_STORAGE',
                'UNDER_REPAIR',
                'RETIRED',
                'DISPOSED',
            ])->default('IN_STORAGE');
            $table->enum('condition', ['NEW', 'GOOD', 'FAIR', 'POOR'])->default('GOOD');

            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('employee_id')->nullable();

            // a peripheral / monitor can hang off the computer it is attached to
            $table->unsignedBigInteger('parent_asset_id')->nullable();

            $table->timestamps();

            // indexes
            $table->index('category_id');
            $table->index('location_id');
            $table->index('employee_id');
            $table->index('parent_asset_id');
            $table->index('status');
            $table->index('warranty_end');

            // foreign keys
            $table->foreign('category_id')->references('id')->on('asset_categories')->restrictOnDelete();
            $table->foreign('location_id')->references('id')->on('asset_locations')->restrictOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('parent_asset_id')->references('id')->on('assets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['location_id']);
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['parent_asset_id']);
        });

        Schema::dropIfExists('assets');
    }
};
