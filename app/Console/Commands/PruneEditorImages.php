<?php

namespace App\Console\Commands;

use App\Models\Notice;
use App\Models\Post;
use App\Support\EditorImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Editor images are uploaded before the post is saved, so abandoned drafts leave files behind.
 * Remove images that no post or notice (including soft-deleted ones) references any more.
 */
class PruneEditorImages extends Command
{
    protected $signature = 'posts:prune-images {--hours=24 : Keep files younger than this, they may belong to a post being written}';

    protected $description = 'Delete editor images that no post or notice uses';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $cutoff = now()->subHours((int) $this->option('hours'))->getTimestamp();

        $used = [];
        $collect = function (string $content) use (&$used) {
            preg_match_all('~/storage/('.preg_quote(EditorImage::DIRECTORY, '~').'/[^"\'\s<>)]+)~', $content, $m);
            foreach ($m[1] as $path) {
                $used[$path] = true;
            }
        };

        Post::withTrashed()->where('content', 'like', '%/storage/'.EditorImage::DIRECTORY.'/%')
            ->select('id', 'content')->chunkById(200, fn ($posts) => $posts->each(fn ($post) => $collect($post->content)));
        Notice::withTrashed()->where('content', 'like', '%/storage/'.EditorImage::DIRECTORY.'/%')
            ->select('id', 'content')->chunkById(200, fn ($notices) => $notices->each(fn ($notice) => $collect($notice->content)));

        $deleted = 0;

        foreach ($disk->allFiles(EditorImage::DIRECTORY) as $path) {
            if (! isset($used[$path]) && $disk->lastModified($path) < $cutoff) {
                $disk->delete($path);
                $deleted++;
            }
        }

        $this->info("Deleted {$deleted} unused editor image(s).");

        return self::SUCCESS;
    }
}
