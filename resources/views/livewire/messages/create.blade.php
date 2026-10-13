<?php

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new class extends Component {
    /** Messages a user may send per minute. */
    public const PER_MINUTE = 10;

    #[Url(as: 'to', except: '')]
    public string $recipient = '';

    public string $title = '';

    public string $content = '';

    public function mount(): void
    {
        $replyTo = request()->integer('reply');

        if ($replyTo) {
            $original = Message::with('sender')->findOrFail($replyTo);
            abort_unless($original->recipient_id === auth()->id() && $original->isVisibleTo(auth()->user()), 404);

            $this->recipient = $original->sender?->name ?? '';
            $this->title = Str::startsWith($original->title, 'Re: ') ? $original->title : Str::limit('Re: '.$original->title, 100, '');
        }
    }

    public function rendering(View $view): void
    {
        $view->title(__('New message'));
    }

    public function send(): void
    {
        $this->validate([
            'recipient' => ['required', 'string', Rule::exists(User::class, 'name')->whereNull('deleted_at'), Rule::notIn([auth()->user()->name])],
            'title' => ['required', 'string', 'max:100'],
            'content' => ['required', 'string', 'max:5000'],
        ], [
            'recipient.exists' => __('No member has this nickname.'),
            'recipient.not_in' => __('You cannot send a message to yourself.'),
        ], [
            'recipient' => __('Recipient'),
            'title' => __('Title'),
            'content' => __('Content'),
        ]);

        $key = 'send-message:'.auth()->id();

        if (RateLimiter::tooManyAttempts($key, self::PER_MINUTE)) {
            throw ValidationException::withMessages([
                'content' => __('You are sending messages too quickly. Try again in :seconds seconds.', ['seconds' => RateLimiter::availableIn($key)]),
            ]);
        }

        RateLimiter::hit($key);

        $message = new Message([
            'recipient_id' => User::where('name', $this->recipient)->value('id'),
            'title' => trim($this->title),
            'content' => trim($this->content),
        ]);
        $message->sender()->associate(auth()->user());
        $message->save();

        session()->flash('status', __('Message sent.'));

        $this->redirectRoute('messages.index', ['box' => 'sent'], navigate: true);
    }
}; ?>

<div class="flex max-w-3xl flex-col gap-6">
    <flux:heading size="xl" level="1">{{ __('New message') }}</flux:heading>

    <form wire:submit="send" class="flex flex-col gap-6">
        <flux:input wire:model="recipient" :label="__('Recipient')" :description="__('Enter the nickname of the member.')" required />
        <flux:input wire:model="title" :label="__('Title')" maxlength="100" required />
        <flux:textarea wire:model="content" :label="__('Content')" rows="8" maxlength="5000" required />

        <div class="flex justify-end gap-2">
            <flux:button :href="route('messages.index')" wire:navigate>{{ __('Cancel') }}</flux:button>
            <flux:button type="submit" variant="primary" icon="paper-airplane">{{ __('Send') }}</flux:button>
        </div>
    </form>
</div>
