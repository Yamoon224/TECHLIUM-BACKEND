<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Colour choices offered to customers: [{"name": "Bleu ciel", "hex": "#a9c7e8"}, ...]
            $table->json('colors')->nullable()->after('attributes');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('colors');
        });
    }
};
