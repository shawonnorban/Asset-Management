<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_locations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('location_name', 50);
            $table->timestamps();

            $table->unique('location_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_locations');
    }
};
