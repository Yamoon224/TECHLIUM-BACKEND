<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
<<<<<<< HEAD
            // Snapshot of the offer's vendor (null = TechLium's own stock).
=======
            // Snapshot of the offer's vendor (null = TechniLium's own stock).
>>>>>>> 2a32efc33a7ad925aaa66b3778733f66317cb71f
            // Groups lines into per-vendor sub-orders; no FK (cahier §9.1).
            $table->unsignedBigInteger('vendor_id')->nullable()->after('offer_id');
            $table->index('vendor_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('delivered_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn('delivered_at'));
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['vendor_id']);
            $table->dropColumn('vendor_id');
        });
    }
};
