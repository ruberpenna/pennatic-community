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
        // RSS/Atom feeds whose new items are posted to a board by the news bot (news:fetch).
        Schema::create('feed_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained()->cascadeOnDelete();
            $table->string('name', 50);
            $table->string('url', 500);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('max_items')->default(15); // per run
            $table->timestamp('last_fetched_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });

        // Items already posted, so the same story is never posted twice.
        Schema::create('feed_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_source_id')->constrained()->cascadeOnDelete();
            $table->char('guid_hash', 64);
            $table->foreignId('post_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['feed_source_id', 'guid_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_items');
        Schema::dropIfExists('feed_sources');
    }
};
