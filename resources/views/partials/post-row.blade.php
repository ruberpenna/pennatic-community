<tr @class(['bg-zinc-50 dark:bg-zinc-900/50' => $post->is_pinned])>
    <td class="py-2 text-center text-zinc-500 max-sm:hidden">
        @if ($post->is_pinned)
            <flux:badge size="sm" color="amber">{{ __('Pinned') }}</flux:badge>
        @else
            {{ $number }}
        @endif
    </td>
    <td class="py-2">
        <a href="{{ route('posts.show', $post) }}" class="hover:underline" wire:navigate>
            @if ($post->subject)
                <span class="text-zinc-500">[{{ $post->subject }}]</span>
            @endif
            {{ $post->title }}
            @if ($post->comments_count)
                <span class="text-xs text-zinc-500">[{{ $post->comments_count }}]</span>
            @endif
        </a>
    </td>
    <td class="truncate py-2">{{ $post->user?->name ?? __('Unknown') }}</td>
    <td class="py-2 text-center text-zinc-500">
        <time datetime="{{ $post->created_at->toIso8601String() }}">
            {{ $post->created_at->isToday() ? $post->created_at->format('H:i') : $post->created_at->format('y.m.d') }}
        </time>
    </td>
    <td class="py-2 text-center text-zinc-500 max-sm:hidden">{{ number_format($post->view_count) }}</td>
</tr>
