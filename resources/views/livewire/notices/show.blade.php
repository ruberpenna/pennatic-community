<?php

use App\Models\Notice;
use Illuminate\View\View;
use Livewire\Volt\Component;

new class extends Component {
    public Notice $notice;

    public function mount(Notice $notice): void
    {
        $this->notice = $notice;
    }

    public function rendering(View $view): void
    {
        $view->title($this->notice->title);
    }
}; ?>

<article class="flex max-w-3xl flex-col gap-6">
    <header class="flex flex-col gap-2 border-b border-zinc-200 pb-4 dark:border-zinc-700">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item :href="route('notices.index')" wire:navigate>{{ __('Notices') }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
        <flux:heading size="xl" level="1">{{ $notice->title }}</flux:heading>
        <time class="text-sm text-zinc-500" datetime="{{ $notice->created_at->toIso8601String() }}">{{ $notice->created_at->format('Y.m.d H:i') }}</time>
    </header>

    <div class="post-content">
        {!! $notice->content !!}
    </div>

    <div>
        <flux:button :href="route('notices.index')" icon="list-bullet" wire:navigate>{{ __('List') }}</flux:button>
    </div>
</article>
