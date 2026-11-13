<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Snapshot of the colour the customer picked (name + swatch), like the price and name.
            $table->string('color_name', 40)->nullable()->after('brand');
            $table->char('color_hex', 7)->nullable()->after('color_name');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['color_name', 'color_hex']);
        });
    }
};
