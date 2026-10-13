<?php

use App\Models\Message;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    #[Url(except: 'inbox')]
    public string $box = 'inbox';

    /** @var list<int> */
    public array $selected = [];

    public function rendering(View $view): void
    {
        $view->title(__('Messages'));
    }

    public function updatedBox(): void
    {
        $this->reset('selected');
        $this->resetPage();
    }

    public function deleteSelected(): void
    {
        $user = auth()->user();

        Message::whereKey($this->selected)
            ->get()
            ->filter(fn (Message $message) => $message->isVisibleTo($user))
            ->each(fn (Message $message) => $message->deleteFor($user));

        $this->reset('selected');
    }

    public function with(): array
    {
        $user = auth()->user();

        $messages = $this->box === 'sent'
            ? Message::sentBy($user)->with('recipient')
            : Message::inboxOf($user)->with('sender');

        return [
            'messages' => $messages->latest('id')->paginate(20),
        ];
    }
}; ?>

<div class="flex max-w-3xl flex-col gap-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <flux:heading size="xl" level="1">{{ __('Messages') }}</flux:heading>
        <flux:button variant="primary" icon="pencil-square" :href="route('messages.create')" wire:navigate>{{ __('New message') }}</flux:button>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle" :heading="session('status')" />
    @endif

    <div class="flex flex-wrap items-center justify-between gap-2">
        <flux:radio.group wire:model.live="box" variant="segmented" class="max-w-xs">
            <flux:radio value="inbox" :label="__('Inbox')" />
            <flux:radio value="sent" :label="__('Sent')" />
        </flux:radio.group>

        <flux:button size="sm" variant="danger" icon="trash" wire:click="deleteSelected" :disabled="empty($selected)" wire:confirm="{{ __('Delete the selected messages?') }}">
            {{ __('Delete selected') }}
        </flux:button>
    </div>

    <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
        @forelse ($messages as $message)
            @php($other = $box === 'sent' ? $message->recipient : $message->sender)
            <li class="flex items-center gap-3 py-3" wire:key="message-{{ $message->id }}">
                <flux:checkbox wire:model.live="selected" :value="$message->id" :aria-label="__('Select')" />
                <div class="min-w-0 flex-1">
                    <a href="{{ route('messages.show', $message) }}" @class(['block truncate hover:underline', 'font-semibold' => $box === 'inbox' && ! $message->read_at]) wire:navigate>
                        {{ $message->title }}
                    </a>
                    <div class="flex gap-2 text-xs text-zinc-500">
                        <span>{{ $box === 'sent' ? __('To') : __('From') }} {{ $other?->name ?? __('Unknown') }}</span>
                        <time datetime="{{ $message->created_at->toIso8601String() }}">· {{ $message->created_at->format('Y.m.d H:i') }}</time>
                        @if ($box === 'sent')
                            <span>· {{ $message->read_at ? __('Read') : __('Unread') }}</span>
                        @endif
                    </div>
                </div>
            </li>
        @empty
            <li class="py-10 text-center text-zinc-500">{{ __('No messages.') }}</li>
        @endforelse
    </ul>

    {{ $messages->links() }}
</div>
