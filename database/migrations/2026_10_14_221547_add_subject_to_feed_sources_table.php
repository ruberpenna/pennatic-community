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
            $table->string('subject', 30)->nullable()->after('name'); // post prefix given to every item
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feed_sources', function (Blueprint $table) {
            $table->dropColumn('subject');
        });
    }
};
