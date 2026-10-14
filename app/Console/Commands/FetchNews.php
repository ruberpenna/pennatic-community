<?php

namespace App\Console\Commands;

use App\Models\FeedSource;
use App\Models\Post;
use App\Models\User;
use App\Support\FeedReader;
use App\Support\PostContent;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Posts new items from active feed sources to their boards as the news bot.
 *
 * Only the headline, the feed's own short summary and a link to the original are posted,
 * never the full article.
 */
class FetchNews extends Command
{
    protected $signature = 'news:fetch {--source= : Only this feed source id}';

    protected $description = 'Post new RSS/Atom items to their boards';

    public const BOT_USERNAME = 'newsbot';

    public const SUMMARY_LENGTH = 300;

    public function handle(): int
    {
        $sources = FeedSource::with('board')
            ->when($this->option('source'), fn ($query, $id) => $query->whereKey($id), fn ($query) => $query->where('is_active', true))
            ->get();

        $bot = self::bot();
        $failed = 0;

        foreach ($sources as $source) {
            try {
                $posted = $this->fetch($source, $bot);
                $source->forceFill(['last_fetched_at' => now(), 'last_error' => null])->save();
                $this->info("{$source->name}: {$posted} new post(s)");
            } catch (\Throwable $e) {
                $failed++;
                $source->forceFill(['last_fetched_at' => now(), 'last_error' => Str::limit($e->getMessage(), 500)])->save();
                $this->error("{$source->name}: {$e->getMessage()}");
                report($e);
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function fetch(FeedSource $source, User $bot): int
    {
        $response = Http::timeout(20)
            ->withUserAgent('PennaNewsBot/1.0 (+'.config('app.url').')')
            ->get($source->url)
            ->throw();

        $items = array_slice(FeedReader::parse($response->body()), 0, $source->max_items);
        $posted = 0;

        // Oldest first, so newer stories get higher post numbers.
        foreach (array_reverse($items) as $item) {
            $hash = hash('sha256', $item['guid']);

            if ($source->items()->where('guid_hash', $hash)->exists()) {
                continue;
            }

            DB::transaction(function () use ($source, $bot, $item, $hash) {
                $post = new Post([
                    'title' => Str::limit($item['title'], 100, '…'),
                    'content' => self::content($item, $source->name),
                ]);
                $post->board()->associate($source->board);
                $post->user()->associate($bot);
                $post->save();

                $source->items()->create(['guid_hash' => $hash, 'post_id' => $post->id]);
            });

            $posted++;
        }

        return $posted;
    }

    /**
     * @param  array{summary: string, link: string}  $item
     */
    public static function content(array $item, string $sourceName): string
    {
        $summary = Str::limit($item['summary'], self::SUMMARY_LENGTH, '…');

        $html = ($summary !== '' ? '<div>'.e($summary).'</div><div><br></div>' : '')
            .'<div><a href="'.e($item['link']).'">'.e(__('Read the original').' · '.$sourceName).'</a></div>';

        return PostContent::clean($html);
    }

    /**
     * The account news posts are written by. It cannot be logged into (random password nobody knows).
     */
    public static function bot(): User
    {
        $bot = User::withTrashed()->firstOrNew(['username' => self::BOT_USERNAME]);

        if (! $bot->exists) {
            $bot->forceFill([
                'name' => __('News bot'),
                'email' => self::BOT_USERNAME.'@'.(parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost'),
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(64)),
            ])->save();
        }

        return $bot;
    }
}
