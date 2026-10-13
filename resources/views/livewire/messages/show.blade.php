<?php

use App\Models\Message;
use Illuminate\View\View;
use Livewire\Volt\Component;

new class extends Component {
    public Message $message;

    public function mount(Message $message): void
    {
        abort_unless($message->isVisibleTo(auth()->user()), 404);

        $message->markReadBy(auth()->user());
        $this->message = $message->load('sender', 'recipient');
    }

    public function rendering(View $view): void
    {
        $view->title($this->message->title);
    }

    public function delete(): void
    {
        $this->message->deleteFor(auth()->user());

        $this->redirectRoute('messages.index', navigate: true);
    }
}; ?>

<article class="flex max-w-3xl flex-col gap-6">
    <header class="flex flex-col gap-2 border-b border-zinc-200 pb-4 dark:border-zinc-700">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item :href="route('messages.index')" wire:navigate>{{ __('Messages') }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
        <flux:heading size="xl" level="1">{{ $message->title }}</flux:heading>
        <div class="flex flex-wrap gap-x-4 text-sm text-zinc-500">
            <span>{{ __('From') }} {{ $message->sender?->name ?? __('Unknown') }}</span>
            <span>{{ __('To') }} {{ $message->recipient?->name ?? __('Unknown') }}</span>
            <time datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->format('Y.m.d H:i') }}</time>
        </div>
    </header>

    <p class="whitespace-pre-line break-words">{{ $message->content }}</p>

    <footer class="flex flex-wrap justify-between gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
        <flux:button :href="route('messages.index')" icon="list-bullet" wire:navigate>{{ __('List') }}</flux:button>

        <div class="flex gap-2">
            @if ($message->recipient_id === auth()->id() && $message->sender && ! $message->sender->trashed())
                <flux:button icon="arrow-uturn-left" :href="route('messages.create', ['reply' => $message->id])" wire:navigate>{{ __('Reply') }}</flux:button>
            @endif
            <flux:button variant="danger" icon="trash" wire:click="delete" wire:confirm="{{ __('Delete this message?') }}">{{ __('Delete') }}</flux:button>
        </div>
    </footer>
</article>
