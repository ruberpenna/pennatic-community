<?php

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Volt\Component;

new class extends Component {
    /** Inquiries a user may send per hour. */
    public const PER_HOUR = 5;

    public string $title = '';

    public string $content = '';

    public function rendering(View $view): void
    {
        $view->title(__('Contact the admin'));
    }

    public function send(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:90'],
            'content' => ['required', 'string', 'max:5000'],
        ], attributes: [
            'title' => __('Title'),
            'content' => __('Content'),
        ]);

        $key = 'contact:'.auth()->id();

        if (RateLimiter::tooManyAttempts($key, self::PER_HOUR)) {
            throw ValidationException::withMessages([
                'content' => __('Too many inquiries. Try again in :minutes minutes.', ['minutes' => ceil(RateLimiter::availableIn($key) / 60)]),
            ]);
        }

        $admins = User::where('role', 'admin')->pluck('id');

        if ($admins->isEmpty()) {
            throw ValidationException::withMessages(['content' => __('There is no admin to receive inquiries right now.')]);
        }

        RateLimiter::hit($key, 3600);

        // The inquiry arrives in every admin's inbox as a message they can reply to.
        DB::transaction(function () use ($admins) {
            foreach ($admins as $adminId) {
                $message = new Message([
                    'recipient_id' => $adminId,
                    'title' => '['.__('Inquiry').'] '.trim($this->title),
                    'content' => trim($this->content),
                ]);
                $message->sender()->associate(auth()->user());
                $message->save();
            }
        });

        $this->reset('title', 'content');
        session()->flash('status', __('Your inquiry has been sent. The answer will arrive as a message.'));
    }
}; ?>

<div class="flex max-w-3xl flex-col gap-6">
    <div>
        <flux:heading size="xl" level="1">{{ __('Contact the admin') }}</flux:heading>
        <flux:subheading>{{ __('Questions, reports and suggestions are welcome. The answer will arrive as a message.') }}</flux:subheading>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle" :heading="session('status')" />
    @endif

    <form wire:submit="send" class="flex flex-col gap-6">
        <flux:input wire:model="title" :label="__('Title')" maxlength="90" required />
        <flux:textarea wire:model="content" :label="__('Content')" rows="8" maxlength="5000" required />
        <div class="flex justify-end">
            <flux:button type="submit" variant="primary" icon="paper-airplane">{{ __('Send') }}</flux:button>
        </div>
    </form>
</div>
