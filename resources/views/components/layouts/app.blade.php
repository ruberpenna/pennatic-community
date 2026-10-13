<x-layouts.app.sidebar>
    <flux:main>
        @auth
            @unless (auth()->user()->hasVerifiedEmail())
                <flux:callout variant="warning" icon="envelope" class="mb-6">
                    <flux:callout.heading>{{ __('Please verify your email address.') }}</flux:callout.heading>
                    <flux:callout.text>
                        {{ __('Until your email is verified you can read, but not write posts, comments or messages.') }}
                        <flux:link :href="route('verification.notice')" wire:navigate>{{ __('Resend verification email') }}</flux:link>
                    </flux:callout.text>
                </flux:callout>
            @endunless
        @endauth

        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>
