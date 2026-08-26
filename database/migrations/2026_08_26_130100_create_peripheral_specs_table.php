<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peripheral_specs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('asset_id')->unique();

            $table->enum('peripheral_type', [
                'MONITOR',
                'KEYBOARD',
                'MOUSE',
                'UPS',
                'DOCKING_STATION',
                'HEADSET',
                'WEBCAM',
                'SCANNER',
                'PROJECTOR',
                'OTHER',
            ])->default('OTHER');

            $table->enum('connection', ['USB', 'HDMI', 'DISPLAYPORT', 'VGA', 'DVI', 'BLUETOOTH', 'WIRELESS', 'PS2', 'OTHER'])
                  ->nullable();

            // monitor / projector only
            $table->decimal('screen_size_inch', 4, 1)->nullable();
            $table->string('resolution', 20)->nullable();      // 1920x1080
            $table->string('panel_type', 20)->nullable();      // IPS, VA, TN
            $table->unsignedSmallInteger('refresh_rate_hz')->nullable();

            // UPS only
            $table->unsignedInteger('capacity_va')->nullable();
            $table->unsignedSmallInteger('backup_minutes')->nullable();

            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('peripheral_type');

            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('peripheral_specs', function (Blueprint $table) {
            $table->dropForeign(['asset_id']);
        });

        Schema::dropIfExists('peripheral_specs');
    }
};
