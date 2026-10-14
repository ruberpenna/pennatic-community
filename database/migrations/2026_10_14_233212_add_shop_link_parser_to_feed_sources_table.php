<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('feed_sources', function (Blueprint $table) {
            // Key of App\Support\ShopLink::PARSERS; when set, each new item's page is read for the shop link.
            $table->string('shop_link_parser', 30)->nullable()->after('fetch_interval_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feed_sources', function (Blueprint $table) {
            $table->dropColumn('shop_link_parser');
        });
    }
};
