<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('computer_specs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('asset_id')->unique();

            $table->enum('form_factor', ['DESKTOP', 'LAPTOP', 'ALL_IN_ONE', 'WORKSTATION', 'SERVER'])
                  ->default('DESKTOP');

            // processing
            $table->string('cpu', 120)->nullable();          // e.g. Intel Core i5-11400
            $table->unsignedSmallInteger('cpu_cores')->nullable();
            $table->string('gpu', 120)->nullable();
            $table->string('motherboard', 120)->nullable();
            $table->string('psu', 60)->nullable();

            // memory
            $table->unsignedSmallInteger('ram_gb')->nullable();
            $table->string('ram_type', 20)->nullable();       // DDR4, DDR5

            // storage
            $table->enum('storage_type', ['HDD', 'SSD', 'NVME', 'HYBRID'])->nullable();
            $table->unsignedInteger('storage_gb')->nullable();
            $table->enum('secondary_storage_type', ['HDD', 'SSD', 'NVME'])->nullable();
            $table->unsignedInteger('secondary_storage_gb')->nullable();

            // network identity
            $table->string('hostname', 60)->nullable();
            $table->string('domain', 60)->nullable();
            $table->string('mac_address', 17)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->enum('ip_type', ['STATIC', 'DHCP'])->nullable();

            // software
            $table->string('os', 60)->nullable();             // Windows 11 Pro
            $table->string('os_version', 40)->nullable();     // 23H2
            $table->string('os_license_key', 60)->nullable();
            $table->string('office_license_key', 60)->nullable();
            $table->string('antivirus', 60)->nullable();

            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('hostname');
            $table->index('form_factor');

            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('computer_specs', function (Blueprint $table) {
            $table->dropForeign(['asset_id']);
        });

        Schema::dropIfExists('computer_specs');
    }
};
