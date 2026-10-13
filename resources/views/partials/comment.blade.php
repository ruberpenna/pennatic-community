{{-- One comment inside posts.comments; expects $comment, $isReply, $editingId, $myVotes. --}}
@if ($comment->trashed())
    <p class="text-sm text-zinc-500">{{ __('This comment has been deleted.') }}</p>
@else
    <div class="flex flex-col gap-1">
        <div class="flex flex-wrap items-center gap-2 text-sm">
            <flux:avatar size="xs" :src="$comment->user?->avatarUrl()" :initials="$comment->user?->initials() ?? '?'" />
            <span class="font-medium">{{ $comment->user?->name ?? __('Unknown') }}</span>
            <time class="text-xs text-zinc-500" datetime="{{ $comment->created_at->toIso8601String() }}">{{ $comment->created_at->format('Y.m.d H:i') }}</time>
            @if ($comment->updated_at->gt($comment->created_at->addMinute()))
                <span class="text-xs text-zinc-500">({{ __('Edited') }})</span>
            @endif
        </div>

        @if ($editingId === $comment->id)
            <form method="post" wire:submit="update" class="flex flex-col gap-2">
                <flux:textarea wire:model="editContent" rows="2" :maxlength="1000" :aria-label="__('Comment')" />
                <div class="flex justify-end gap-2">
                    <flux:button size="sm" variant="ghost" wire:click="$set('editingId', null)">{{ __('Cancel') }}</flux:button>
                    <flux:button size="sm" type="submit" variant="primary">{{ __('Save') }}</flux:button>
                </div>
            </form>
        @else
            <p class="whitespace-pre-line break-words text-sm">{{ $comment->content }}</p>

            <div class="flex flex-wrap items-center gap-1 text-xs">
                @include('partials.vote-buttons', [
                    'up' => $comment->upvotes_count,
                    'down' => $comment->downvotes_count,
                    'mine' => (int) ($myVotes[$comment->id] ?? 0),
                    'action' => "vote({$comment->id}, %d)",
                    'canVote' => auth()->guest() || auth()->user()->can('vote', $comment),
                    'size' => 'xs',
                ])

                <flux:button size="xs" variant="ghost" wire:click="startReply({{ $comment->id }})">{{ __('Reply') }}</flux:button>

                @can('update', $comment)
                    <flux:button size="xs" variant="ghost" wire:click="startEdit({{ $comment->id }})">{{ __('Edit') }}</flux:button>
                @endcan

                @can('delete', $comment)
                    <flux:button size="xs" variant="ghost" wire:click="delete({{ $comment->id }})" wire:confirm="{{ __('Delete this comment?') }}">{{ __('Delete') }}</flux:button>
                @endcan
            </div>
        @endif
    </div>
@endif
