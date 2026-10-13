<?php

use App\Models\Notice;
use Illuminate\View\View;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public function rendering(View $view): void
    {
        $view->title(__('Notices'));
    }

    public function with(): array
    {
        return [
            'notices' => Notice::latest()->paginate(20),
        ];
    }
}; ?>

<div class="flex max-w-3xl flex-col gap-6">
    <flux:heading size="xl" level="1">{{ __('Notices') }}</flux:heading>

    <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
        @forelse ($notices as $notice)
            <li class="flex justify-between gap-4 py-3">
                <a href="{{ route('notices.show', $notice) }}" class="truncate hover:underline" wire:navigate>{{ $notice->title }}</a>
                <time class="shrink-0 text-sm text-zinc-500" datetime="{{ $notice->created_at->toDateString() }}">{{ $notice->created_at->format('Y.m.d') }}</time>
            </li>
        @empty
            <li class="py-10 text-center text-zinc-500">{{ __('No notices yet.') }}</li>
        @endforelse
    </ul>

    {{ $notices->links() }}
</div>
