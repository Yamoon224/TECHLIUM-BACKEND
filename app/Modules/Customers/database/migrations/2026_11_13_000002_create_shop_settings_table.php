<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row: what the storefront says about the shop itself, edited from the back office.
        Schema::create('shop_settings', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 40)->nullable();
            $table->string('whatsapp', 40)->nullable();
            $table->string('email')->nullable();
            $table->string('hours')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('tiktok_url')->nullable();
            $table->text('delivery_info')->nullable();
            $table->text('delivery_info_en')->nullable();
            // [{"fr": "...", "en": "..."}]: the scrolling bar above the header; empty = the storefront's defaults.
            $table->json('topbar_messages')->nullable();
            // ["mtn", "wave", ...]: the means of payment shown in the footer; null = all of them.
            $table->json('payment_methods')->nullable();
            $table->timestamps();
        });

        DB::table('shop_settings')->insert([
            'latitude' => 5.3013244,
            'longitude' => -4.0021515,
            'tiktok_url' => 'https://www.tiktok.com/@techliumgroupe0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_settings');
    }
};
