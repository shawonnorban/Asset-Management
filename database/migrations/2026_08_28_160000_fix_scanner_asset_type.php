<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('asset_categories')
            ->where('category_name', 'Scanner')
            ->update(['asset_type' => 'PERIPHERAL', 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('asset_categories')
            ->where('category_name', 'Scanner')
            ->update(['asset_type' => 'PRINTER', 'updated_at' => now()]);
    }
};
