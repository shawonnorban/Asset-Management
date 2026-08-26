<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('category_name', 50);

            // Drives which specification form an asset of this category gets.
            $table->enum('asset_type', [
                'COMPUTER',
                'PERIPHERAL',
                'PRINTER',
                'NETWORK_DEVICE',
                'OTHER',
            ])->default('OTHER');

            $table->timestamps();

            $table->unique('category_name');
            $table->index('asset_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_categories');
    }
};
