<?php

namespace App\Providers;

use App\Models\Board;
use App\Models\BoardFolder;
use App\Models\Message;
use App\Models\Post;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Cloudflare reaches the proxy over plain HTTP, so the forwarded scheme says "http";
        // generate https URLs (Livewire update endpoint, redirects) whenever the site is configured for https.
        if (str_starts_with(config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        View::composer('components.layouts.app.sidebar', function ($view) {
            $view->with([
                'boardFolders' => BoardFolder::with('boards')->orderBy('sort_order')->orderBy('id')->get(),
                'currentBoardId' => $this->currentBoardId(),
                'unreadMessages' => auth()->check()
                    ? Message::inboxOf(auth()->user())->whereNull('read_at')->count()
                    : 0,
            ]);
        });
    }

    /**
     * The board the current page belongs to, for highlighting the menu.
     * Route parameters may still be raw ids on full-page Livewire routes.
     */
    private function currentBoardId(): ?int
    {
        $route = request()->route();
        $board = $route?->parameter('board');
        $post = $route?->parameter('post');

        return match (true) {
            $board instanceof Board => $board->id,
            is_numeric($board) => (int) $board,
            $post instanceof Post => $post->board_id,
            is_numeric($post) => Post::withTrashed()->whereKey($post)->value('board_id'),
            default => null,
        };
    }
}
