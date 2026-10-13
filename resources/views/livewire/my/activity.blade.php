<?php

use App\Models\Comment;
use App\Models\Post;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    #[Url(except: 'posts')]
    public string $tab = 'posts';

    public function rendering(View $view): void
    {
        $view->title(__('My activity'));
    }

    public function updatedTab(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $user = auth()->user();

        return [
            'items' => $this->tab === 'comments'
                ? Comment::where('user_id', $user->id)
                    ->whereHas('post.board', fn ($query) => $query->visibleTo($user))
                    ->with('post.board')->latest('id')->paginate(20)
                : Post::where('user_id', $user->id)
                    ->whereHas('board', fn ($query) => $query->visibleTo($user))
                    ->with('board')->withCount('comments')->latest('id')->paginate(20),
        ];
    }
}; ?>

<div class="flex max-w-3xl flex-col gap-6">
    <flux:heading size="xl" level="1">{{ __('My activity') }}</flux:heading>

    <flux:radio.group wire:model.live="tab" variant="segmented" class="max-w-xs">
        <flux:radio value="posts" :label="__('Posts')" />
        <flux:radio value="comments" :label="__('Comments')" />
    </flux:radio.group>

    <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
        @forelse ($items as $item)
            @php($post = $item instanceof Post ? $item : $item->post)
            <li class="flex flex-col gap-1 py-3" wire:key="{{ $tab }}-{{ $item->id }}">
                <a href="{{ route('posts.show', $post) }}" class="hover:underline" wire:navigate>
                    @if ($item instanceof Post)
                        {{ $item->title }}
                        @if ($item->comments_count)
                            <span class="text-xs text-zinc-500">[{{ $item->comments_count }}]</span>
                        @endif
                    @else
                        <span class="line-clamp-2 whitespace-pre-line">{{ $item->content }}</span>
                    @endif
                </a>
                <div class="flex gap-2 text-xs text-zinc-500">
                    <span>{{ $post->board->name }}</span>
                    @unless ($item instanceof Post)
                        <span class="truncate">· {{ $post->title }}</span>
                    @endunless
                    <time datetime="{{ $item->created_at->toIso8601String() }}">· {{ $item->created_at->format('Y.m.d H:i') }}</time>
                </div>
            </li>
        @empty
            <li class="py-10 text-center text-zinc-500">
                {{ $tab === 'comments' ? __('You have not written any comments yet.') : __('You have not written any posts yet.') }}
            </li>
        @endforelse
    </ul>

    {{ $items->links() }}
</div>
