<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('software_licenses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 120);                 // Microsoft Office
            $table->string('publisher', 100)->nullable();
            $table->string('version', 40)->nullable();

            $table->enum('license_type', ['PERPETUAL', 'SUBSCRIPTION', 'OEM', 'VOLUME', 'OPEN_SOURCE'])
                  ->default('PERPETUAL');
            $table->string('license_key', 120)->nullable();

            $table->unsignedInteger('seats_total')->default(1);

            $table->string('vendor', 100)->nullable();
            $table->string('invoice_no', 60)->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 18, 2)->nullable();
            $table->date('expiry_date')->nullable();     // subscriptions

            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('name');
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('software_licenses');
    }
};
