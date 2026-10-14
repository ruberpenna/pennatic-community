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
            // null: daily at 07:00; otherwise fetched every N hours (news:fetch --frequent).
            $table->unsignedTinyInteger('fetch_interval_hours')->nullable()->after('max_items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feed_sources', function (Blueprint $table) {
            $table->dropColumn('fetch_interval_hours');
        });
    }
};
