<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Scanner category is a PERIPHERAL, and PeripheralSpec, AssetSpecService,
 * and the asset form all already expose scanner fields - but the table never
 * had the columns, so saving any of them failed with "Unknown column".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peripheral_specs', function (Blueprint $table) {
            $table->enum('scanner_type', ['FLATBED', 'SHEET_FED', 'HANDHELD', 'DRUM', 'OTHER'])
                ->nullable()
                ->after('backup_minutes');
            $table->unsignedInteger('scan_resolution_dpi')->nullable()->after('scanner_type');
            $table->unsignedSmallInteger('scan_speed_ppm')->nullable()->after('scan_resolution_dpi');
            $table->unsignedSmallInteger('feeder_capacity')->nullable()->after('scan_speed_ppm');
            $table->boolean('duplex_scanning')->nullable()->after('feeder_capacity');
        });
    }

    public function down(): void
    {
        Schema::table('peripheral_specs', function (Blueprint $table) {
            $table->dropColumn([
                'scanner_type',
                'scan_resolution_dpi',
                'scan_speed_ppm',
                'feeder_capacity',
                'duplex_scanning',
            ]);
        });
    }
};
