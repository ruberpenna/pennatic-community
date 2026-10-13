<?php

use App\Models\Comment;
use App\Models\CommentVote;
use App\Models\Post;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new class extends Component {
    public const MAX_LENGTH = 1000;

    #[Locked]
    public int $postId;

    public string $content = '';

    public ?int $replyTo = null;

    public string $replyContent = '';

    public ?int $editingId = null;

    public string $editContent = '';

    public function mount(Post $post): void
    {
        $this->postId = $post->id;
    }

    public function add(): void
    {
        if (! $this->ensureLoggedIn()) {
            return;
        }

        $this->validate(['content' => ['required', 'string', 'max:'.self::MAX_LENGTH]], attributes: ['content' => __('Comment')]);

        $this->createComment($this->content);
        $this->reset('content');
    }

    public function startReply(int $commentId): void
    {
        if (! $this->ensureLoggedIn()) {
            return;
        }

        $this->replyTo = $commentId;
        $this->replyContent = '';
    }

    public function reply(): void
    {
        if (! $this->ensureLoggedIn()) {
            return;
        }

        $this->validate(['replyContent' => ['required', 'string', 'max:'.self::MAX_LENGTH]], attributes: ['replyContent' => __('Reply')]);

        // Replies are one level deep: replying to a reply attaches to its parent.
        $target = $this->findComment($this->replyTo);
        $this->createComment($this->replyContent, $target->parent_id ?? $target->id);

        $this->reset('replyTo', 'replyContent');
    }

    public function startEdit(int $commentId): void
    {
        $comment = $this->findComment($commentId);
        $this->authorize('update', $comment);

        $this->editingId = $comment->id;
        $this->editContent = $comment->content;
    }

    public function update(): void
    {
        $comment = $this->findComment($this->editingId);
        $this->authorize('update', $comment);
        $this->validate(['editContent' => ['required', 'string', 'max:'.self::MAX_LENGTH]], attributes: ['editContent' => __('Comment')]);

        $comment->update(['content' => trim($this->editContent)]);

        $this->reset('editingId', 'editContent');
    }

    public function delete(int $commentId): void
    {
        $comment = $this->findComment($commentId);
        $this->authorize('delete', $comment);

        $comment->delete();
        $this->dispatch('comment-count-changed');
    }

    public function vote(int $commentId, int $value): void
    {
        if (! $this->ensureLoggedIn()) {
            return;
        }

        $comment = $this->findComment($commentId);
        $this->authorize('vote', $comment);

        $comment->toggleVote(auth()->user(), $value);
    }

    public function with(): array
    {
        $comments = Comment::withTrashed()
            ->where('post_id', $this->postId)
            ->with('user')
            ->withVoteCounts()
            ->oldest('id')
            ->get();

        $myVotes = auth()->check()
            ? CommentVote::where('user_id', auth()->id())->whereIn('comment_id', $comments->modelKeys())->pluck('value', 'comment_id')
            : collect();

        $replies = $comments->whereNotNull('parent_id')->reject->trashed()->groupBy('parent_id');

        // A deleted comment stays as a placeholder only while it still has replies.
        $threads = $comments->whereNull('parent_id')
            ->filter(fn (Comment $comment) => ! $comment->trashed() || $replies->has($comment->id))
            ->values();

        return [
            'threads' => $threads,
            'replies' => $replies,
            'myVotes' => $myVotes,
            'total' => $comments->reject->trashed()->count(),
        ];
    }

    private function findComment(?int $id): Comment
    {
        return Comment::where('post_id', $this->postId)->findOrFail($id);
    }

    private function createComment(string $content, ?int $parentId = null): void
    {
        abort_unless(Post::whereKey($this->postId)->exists(), 404);

        $comment = new Comment(['content' => trim($content), 'parent_id' => $parentId]);
        $comment->post_id = $this->postId;
        $comment->user()->associate(auth()->user());
        $comment->save();

        $this->dispatch('comment-count-changed');
    }

    private function ensureLoggedIn(): bool
    {
        if (auth()->check()) {
            return true;
        }

        $this->redirectRoute('login', navigate: true);

        return false;
    }
}; ?>

<section class="flex flex-col gap-4">
    <flux:heading size="lg">{{ __('Comments') }} <span class="text-zinc-500">{{ $total }}</span></flux:heading>

    <ul class="flex flex-col divide-y divide-zinc-200 dark:divide-zinc-700">
        @forelse ($threads as $comment)
            <li wire:key="comment-{{ $comment->id }}" class="py-3">
                @include('partials.comment', ['comment' => $comment, 'isReply' => false])

                @if ($replies->has($comment->id))
                    <ul class="mt-3 flex flex-col gap-3 border-s-2 border-zinc-200 ps-4 dark:border-zinc-700">
                        @foreach ($replies[$comment->id] as $reply)
                            <li wire:key="comment-{{ $reply->id }}">
                                @include('partials.comment', ['comment' => $reply, 'isReply' => true])
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($replyTo !== null && ($replyTo === $comment->id || $replies->get($comment->id)?->contains('id', $replyTo)))
                    <form wire:submit="reply" class="mt-3 flex flex-col gap-2 ps-4">
                        <flux:textarea wire:model="replyContent" rows="2" :maxlength="1000" :placeholder="__('Write a reply')" :aria-label="__('Reply')" />
                        <div class="flex justify-end gap-2">
                            <flux:button size="sm" variant="ghost" wire:click="$set('replyTo', null)">{{ __('Cancel') }}</flux:button>
                            <flux:button size="sm" type="submit" variant="primary">{{ __('Reply') }}</flux:button>
                        </div>
                    </form>
                @endif
            </li>
        @empty
            <li class="py-6 text-center text-sm text-zinc-500">{{ __('No comments yet.') }}</li>
        @endforelse
    </ul>

    @auth
        <form wire:submit="add" class="flex flex-col gap-2">
            <flux:textarea wire:model="content" rows="3" :maxlength="1000" :placeholder="__('Write a comment')" :aria-label="__('Comment')" />
            <div class="flex justify-end">
                <flux:button type="submit" variant="primary">{{ __('Post comment') }}</flux:button>
            </div>
        </form>
    @else
        <p class="rounded-lg border border-zinc-200 p-4 text-center text-sm text-zinc-500 dark:border-zinc-700">
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>{{ __(' to write a comment.') }}
        </p>
    @endauth
</section>
