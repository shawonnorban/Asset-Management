<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('printer_specs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('asset_id')->unique();

            $table->enum('printer_type', ['LASER', 'INKJET', 'DOT_MATRIX', 'THERMAL', 'PLOTTER'])
                  ->default('LASER');
            $table->boolean('is_color')->default(false);
            $table->boolean('is_multifunction')->default(false);   // scan / copy / fax
            $table->boolean('supports_duplex')->default(false);
            $table->string('max_paper_size', 20)->nullable();       // A4, A3, Legal

            // consumables - what IT actually needs to reorder
            $table->string('toner_model', 60)->nullable();
            $table->string('drum_model', 60)->nullable();
            $table->unsignedInteger('monthly_duty_cycle')->nullable();

            // network identity (network printers)
            $table->enum('connection', ['USB', 'ETHERNET', 'WIFI', 'SHARED'])->nullable();
            $table->string('hostname', 60)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('mac_address', 17)->nullable();
            $table->enum('ip_type', ['STATIC', 'DHCP'])->nullable();
            $table->string('management_url', 191)->nullable();

            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('printer_type');

            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('printer_specs', function (Blueprint $table) {
            $table->dropForeign(['asset_id']);
        });

        Schema::dropIfExists('printer_specs');
    }
};
