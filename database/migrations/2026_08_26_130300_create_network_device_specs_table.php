<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('network_device_specs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('asset_id')->unique();

            $table->enum('device_role', [
                'ROUTER',
                'SWITCH',
                'ACCESS_POINT',
                'FIREWALL',
                'MODEM',
                'NAS',
                'SERVER',
                'OTHER',
            ])->default('SWITCH');

            // addressing
            $table->string('hostname', 60)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('mac_address', 17)->nullable();
            $table->enum('ip_type', ['STATIC', 'DHCP'])->nullable();
            $table->string('subnet_mask', 45)->nullable();
            $table->string('gateway', 45)->nullable();
            $table->string('vlan', 40)->nullable();

            // capability
            $table->unsignedSmallInteger('port_count')->nullable();
            $table->string('port_speed', 20)->nullable();      // 1Gbps, 10Gbps
            $table->boolean('is_managed')->default(false);
            $table->boolean('supports_poe')->default(false);
            $table->string('wifi_standard', 20)->nullable();   // WiFi 6, 802.11ac

            // administration
            $table->string('firmware_version', 40)->nullable();
            $table->string('management_url', 191)->nullable();
            $table->string('rack_position', 40)->nullable();

            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('device_role');
            $table->index('ip_address');

            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('network_device_specs', function (Blueprint $table) {
            $table->dropForeign(['asset_id']);
        });

        Schema::dropIfExists('network_device_specs');
    }
};
