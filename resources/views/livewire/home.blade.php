<?php

use App\Models\Board;
use App\Models\Notice;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        return [
            'notices' => Notice::latest()->limit(5)->get(),
            'boards' => Board::query()
                ->join('board_folders', 'board_folders.id', '=', 'boards.board_folder_id')
                ->orderBy('board_folders.sort_order')
                ->orderBy('boards.sort_order')
                ->select('boards.*')
                ->get()
                ->each(fn (Board $board) => $board->setRelation(
                    'posts',
                    $board->posts()->with('user')->withCount('comments')->latest('id')->limit(5)->get(),
                )),
        ];
    }
}; ?>

<div class="flex flex-col gap-8">
    @guest
        <div class="flex flex-col gap-1">
            <flux:heading size="xl" level="1">{{ config('app.name') }}</flux:heading>
            <flux:subheading>{{ __('A community for sharing stories') }}</flux:subheading>
        </div>
    @endguest

    @if ($notices->isNotEmpty())
        <section class="flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">
                    <a href="{{ route('notices.index') }}" wire:navigate>{{ __('Notices') }}</a>
                </flux:heading>
                <flux:link :href="route('notices.index')" variant="subtle" class="text-sm" wire:navigate>{{ __('More') }}</flux:link>
            </div>
            <ul class="divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                @foreach ($notices as $notice)
                    <li class="flex justify-between gap-4 py-2">
                        <a href="{{ route('notices.show', $notice) }}" class="truncate hover:underline" wire:navigate>{{ $notice->title }}</a>
                        <time class="shrink-0 text-zinc-500" datetime="{{ $notice->created_at->toDateString() }}">
                            {{ $notice->created_at->format('Y.m.d') }}
                        </time>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <div class="grid gap-8 md:grid-cols-2">
        @foreach ($boards as $board)
            <section class="flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">
                        <a href="{{ route('boards.show', $board) }}" wire:navigate>{{ $board->name }}</a>
                    </flux:heading>
                    <flux:link :href="route('boards.show', $board)" variant="subtle" class="text-sm" wire:navigate>{{ __('More') }}</flux:link>
                </div>

                <ul class="divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    @forelse ($board->posts as $post)
                        <li class="flex justify-between gap-4 py-2">
                            <a href="{{ route('posts.show', $post) }}" class="truncate hover:underline" wire:navigate>
                                {{ $post->title }}
                                @if ($post->comments_count)
                                    <span class="text-zinc-500">[{{ $post->comments_count }}]</span>
                                @endif
                            </a>
                            <time class="shrink-0 text-zinc-500" datetime="{{ $post->created_at->toDateString() }}">
                                {{ $post->created_at->format('m.d') }}
                            </time>
                        </li>
                    @empty
                        <li class="py-2 text-zinc-500">{{ __('No posts yet.') }}</li>
                    @endforelse
                </ul>
            </section>
        @endforeach
    </div>
</div>
