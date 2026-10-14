<tr @class(['bg-zinc-50 dark:bg-zinc-900/50' => $post->is_pinned])>
    <td class="py-2 text-center text-zinc-500 max-sm:hidden">
        @if ($post->is_pinned)
            <flux:badge size="sm" color="amber">{{ __('Pinned') }}</flux:badge>
        @else
            {{ $number }}
        @endif
    </td>
    <td class="py-2 pe-2">
        <a href="{{ route('posts.show', $post) }}" class="break-words hover:underline" wire:navigate>
            @if ($post->is_pinned)
                {{-- The number column (with the pinned badge) is hidden on phones. --}}
                <flux:badge size="sm" color="amber" class="sm:hidden">{{ __('Pinned') }}</flux:badge>
            @endif
            @if ($post->subject)
                <span class="text-zinc-500">[{{ $post->subject }}]</span>
            @endif
            {{ $post->title }}
            @if ($post->comments_count)
                <span class="text-xs text-zinc-500">[{{ $post->comments_count }}]</span>
            @endif
        </a>
    </td>
    <td class="truncate py-2 max-lg:text-xs">{{ $post->user?->name ?? __('Unknown') }}</td>
    <td class="py-2 text-center text-zinc-500 max-lg:text-xs">
        <time datetime="{{ $post->created_at->toIso8601String() }}">
            @if ($post->created_at->isToday())
                {{ $post->created_at->format('H:i') }}
            @else
                <span class="lg:hidden">{{ $post->created_at->format('m.d') }}</span>
                <span class="max-lg:hidden">{{ $post->created_at->format('y.m.d') }}</span>
            @endif
        </time>
    </td>
    <td class="py-2 text-center text-zinc-500 max-lg:hidden">{{ number_format($post->view_count) }}</td>
    <td class="py-2 text-center text-zinc-500 max-lg:hidden">{{ $post->upvotes_count ?? 0 }}</td>
</tr>
