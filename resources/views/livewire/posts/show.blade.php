<?php

use App\Models\Post;
use Illuminate\Support\Number;
use Illuminate\View\View;
use Livewire\Volt\Component;

new class extends Component {
    public Post $post;

    public function mount(Post $post): void
    {
        $post->board->ensureVisibleTo(auth()->user());

        $this->post = $post->load(['board.folder', 'user', 'attachments']);
        $this->post->recordView(session()->driver());
    }

    public function rendering(View $view): void
    {
        $view->title($this->post->title);
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->post);

        $board = $this->post->board;
        $this->post->delete();

        $this->redirectRoute('boards.show', $board, navigate: true);
    }

    public function vote(int $value): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $this->authorize('vote', $this->post);

        $this->post->toggleVote(auth()->user(), $value);
    }

    public function with(): array
    {
        return [
            'upvotes' => $this->post->votes()->where('value', 1)->count(),
            'downvotes' => $this->post->votes()->where('value', -1)->count(),
            'myVote' => $this->post->voteOf(auth()->user()),
        ];
    }

    public function togglePin(): void
    {
        $this->authorize('pin', $this->post);

        $this->post->is_pinned = ! $this->post->is_pinned;
        $this->post->save();
    }
}; ?>

<article class="flex flex-col gap-6">
    <header class="flex flex-col gap-2 border-b border-zinc-200 pb-4 dark:border-zinc-700">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item>{{ $post->board->folder->name }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item :href="route('boards.show', $post->board)" wire:navigate>{{ $post->board->name }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <flux:heading size="xl" level="1">
            @if ($post->subject)
                <span class="text-zinc-500">[{{ $post->subject }}]</span>
            @endif
            {{ $post->title }}
        </flux:heading>

        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-zinc-500">
            <span class="flex items-center gap-2">
                <flux:avatar size="xs" :src="$post->user?->avatarUrl()" :initials="$post->user?->initials() ?? '?'" />
                @if ($post->user && ! $post->user->trashed() && auth()->id() !== $post->user_id)
                    <flux:dropdown>
                        <button type="button" class="text-zinc-800 hover:underline dark:text-zinc-200">{{ $post->user->name }}</button>
                        <flux:menu>
                            <flux:menu.item icon="envelope" :href="route('messages.create', ['to' => $post->user->name])" wire:navigate>{{ __('Send message') }}</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                @else
                    <span class="text-zinc-800 dark:text-zinc-200">{{ $post->user?->name ?? __('Unknown') }}</span>
                @endif
            </span>
            <time datetime="{{ $post->created_at->toIso8601String() }}">{{ $post->created_at->format('Y.m.d H:i') }}</time>
            <span>{{ __('Views') }} {{ number_format($post->view_count) }}</span>
            @if ($post->updated_at->gt($post->created_at->addMinute()))
                <span>({{ __('Edited') }} {{ $post->updated_at->format('Y.m.d H:i') }})</span>
            @endif
        </div>
    </header>

    <div class="post-content">
        {!! \App\Support\VideoEmbed::render($post->content) !!}
    </div>

    <div class="flex justify-center gap-2">
        @include('partials.vote-buttons', [
            'up' => $upvotes,
            'down' => $downvotes,
            'mine' => $myVote,
            'action' => 'vote(%d)',
            'canVote' => auth()->guest() || auth()->user()->can('vote', $post),
            'size' => 'base',
        ])
    </div>

    @if ($post->attachments->isNotEmpty())
        <section class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading size="sm" class="mb-2">{{ __('Attachments') }}</flux:heading>
            <ul class="flex flex-col gap-1 text-sm">
                @foreach ($post->attachments as $attachment)
                    <li class="flex items-center gap-2">
                        <flux:icon.paper-clip variant="micro" class="text-zinc-500" />
                        @auth
                            <a href="{{ route('attachments.download', $attachment) }}" class="hover:underline">{{ $attachment->original_name }}</a>
                        @else
                            <span>{{ $attachment->original_name }}</span>
                        @endauth
                        <span class="text-zinc-500">({{ Number::fileSize($attachment->size) }})</span>
                    </li>
                @endforeach
            </ul>
            @guest
                <p class="mt-2 text-xs text-zinc-500">{{ __('Log in to download attachments.') }}</p>
            @endguest
        </section>
    @endif

    <footer class="flex flex-wrap justify-between gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
        <flux:button :href="route('boards.show', $post->board)" icon="list-bullet" wire:navigate>{{ __('List') }}</flux:button>

        <div class="flex flex-wrap gap-2">
            @can('pin', $post)
                <flux:button wire:click="togglePin" icon="bookmark">
                    {{ $post->is_pinned ? __('Unpin') : __('Pin') }}
                </flux:button>
            @endcan

            @can('update', $post)
                <flux:button :href="route('posts.edit', $post)" icon="pencil" wire:navigate>{{ __('Edit') }}</flux:button>
            @endcan

            @can('delete', $post)
                <flux:modal.trigger name="delete-post">
                    <flux:button variant="danger" icon="trash">{{ __('Delete') }}</flux:button>
                </flux:modal.trigger>

                <flux:modal name="delete-post" class="min-w-[22rem]">
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg">{{ __('Delete this post?') }}</flux:heading>
                            <flux:subheading>{{ __('Deleted posts cannot be viewed.') }}</flux:subheading>
                        </div>
                        <div class="flex justify-end gap-2">
                            <flux:modal.close>
                                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                            </flux:modal.close>
                            <flux:button variant="danger" wire:click="delete">{{ __('Delete') }}</flux:button>
                        </div>
                    </div>
                </flux:modal>
            @endcan
        </div>
    </footer>

    <livewire:posts.comments :post="$post" :key="'comments-'.$post->id" />
</article>
