<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="config('app.name')" :description="__('A community for sharing stories')" />

        <div class="flex flex-col gap-3">
            @auth
                <flux:button variant="primary" :href="route('dashboard')" wire:navigate>{{ __('Go to community') }}</flux:button>
            @else
                <flux:button variant="primary" :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:button>
                @if (Route::has('register'))
                    <flux:button :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:button>
                @endif
            @endauth
        </div>

        @if ($notices->isNotEmpty())
            <div class="flex flex-col gap-2">
                <flux:heading size="sm">{{ __('Notices') }}</flux:heading>
                <ul class="divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    @foreach ($notices as $notice)
                        <li class="flex justify-between gap-4 py-2">
                            <span class="truncate">{{ $notice->title }}</span>
                            <time class="shrink-0 text-zinc-500" datetime="{{ $notice->created_at->toDateString() }}">
                                {{ $notice->created_at->format('Y.m.d') }}
                            </time>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</x-layouts.auth>
