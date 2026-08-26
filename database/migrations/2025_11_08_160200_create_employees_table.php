<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('employee_code', 32)->unique();
            $table->string('name', 100);
            $table->string('image', 255)->nullable();

            // organisation
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('location_id')->nullable();

            // personal
            $table->string('father_name', 100);
            $table->string('mother_name', 100)->nullable();
            $table->string('nid_number', 30)->nullable()->unique();

            // contact
            $table->text('present_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('mail_address', 150)->nullable();
            $table->string('mobile', 20)->nullable();

            $table->date('join_date')->nullable();

            $table->timestamps();

            // indexes
            $table->index('employee_code');
            $table->index('department_id');
            $table->index('position_id');
            $table->index('location_id');
            $table->index('join_date');

            // foreign keys
            $table->foreign('department_id')->references('id')->on('departments')->restrictOnDelete();
            $table->foreign('position_id')->references('id')->on('positions')->restrictOnDelete();
            $table->foreign('location_id')->references('id')->on('asset_locations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['position_id']);
            $table->dropForeign(['location_id']);
        });

        Schema::dropIfExists('employees');
    }
};
