<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    /** Verification mails a user may request per 10 minutes. */
    public const RESENDS_PER_WINDOW = 3;

    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('home', absolute: false), navigate: true);

            return;
        }

        $key = 'verification-mail:'.Auth::id();

        if (RateLimiter::tooManyAttempts($key, self::RESENDS_PER_WINDOW)) {
            Session::flash('status', 'verification-throttled');
            Session::flash('retry_minutes', (int) ceil(RateLimiter::availableIn($key) / 60));

            return;
        }

        RateLimiter::hit($key, 600);

        try {
            Auth::user()->sendEmailVerificationNotification();
            Session::flash('status', 'verification-link-sent');
        } catch (Throwable $e) {
            report($e);
            Session::flash('status', 'verification-mail-failed');
        }
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Verify your email')" :description="__('Please verify your email address by clicking on the link we just emailed to you.')" />

    <div class="rounded-lg border border-zinc-200 p-4 text-center text-sm dark:border-zinc-700">
        <span class="text-zinc-500">{{ __('Sent to') }}</span>
        <span class="font-medium">{{ auth()->user()->email }}</span>
        <p class="mt-2 text-xs text-zinc-500">{{ __('Until your email is verified you can read, but not write posts, comments or messages.') }}</p>
    </div>

    @if (session('status') === 'verification-link-sent')
        <flux:callout variant="success" icon="check-circle" :heading="__('A new verification link has been sent to the email address you provided during registration.')" />
    @elseif (session('status') === 'verification-mail-failed')
        <flux:callout variant="danger" icon="exclamation-triangle" :heading="__('The verification mail could not be sent. Please try again later.')" />
    @elseif (session('status') === 'verification-throttled')
        <flux:callout variant="warning" icon="clock" :heading="__('Too many requests. Try again in :minutes minutes.', ['minutes' => session('retry_minutes')])" />
    @endif

    <div class="flex flex-col items-center gap-3">
        <flux:button wire:click="sendVerification" variant="primary" class="w-full">
            {{ __('Resend verification email') }}
        </flux:button>

        <div class="flex gap-4 text-sm">
            <flux:link :href="route('settings.profile')" wire:navigate>{{ __('Change email') }}</flux:link>
            <flux:link :href="route('home')" wire:navigate>{{ __('Home') }}</flux:link>
            <button wire:click="logout" type="button" class="text-zinc-600 underline hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                {{ __('Log out') }}
            </button>
        </div>
    </div>
</div>
