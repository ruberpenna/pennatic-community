<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-r border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('home') }}" class="mr-5 flex items-center space-x-2" wire:navigate>
                <x-app-logo class="size-8" href="#"></x-app-logo>
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.item icon="home" :href="route('home')" :current="request()->routeIs('home')" wire:navigate>{{ __('Home') }}</flux:navlist.item>
                <flux:navlist.item icon="megaphone" :href="route('notices.index')" :current="request()->routeIs('notices.*')" wire:navigate>{{ __('Notices') }}</flux:navlist.item>

                @foreach ($boardFolders as $folder)
                    @continue($folder->boards->isEmpty())

                    <flux:navlist.group :heading="$folder->name" class="mt-4 grid">
                        @foreach ($folder->boards as $board)
                            <flux:navlist.item
                                icon="chat-bubble-left-right"
                                :href="route('boards.show', $board)"
                                :current="$currentBoardId === $board->id"
                                wire:navigate
                            >
                                {{ $board->name }}
                            </flux:navlist.item>
                        @endforeach
                    </flux:navlist.group>
                @endforeach
            </flux:navlist>

            <flux:spacer />

            @auth
                <!-- Desktop User Menu -->
                <flux:dropdown position="bottom" align="start">
                    <flux:profile
                        :name="auth()->user()->name"
                        :initials="auth()->user()->initials()"
                        :avatar="auth()->user()->avatarUrl()"
                        icon-trailing="chevrons-up-down"
                    />

                    @include('partials.user-menu')
                </flux:dropdown>
            @else
                <div class="flex flex-col gap-2">
                    <flux:button variant="primary" size="sm" :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:button>
                    <flux:button size="sm" :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:button>
                </div>
            @endauth
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            @auth
                <flux:dropdown position="top" align="end">
                    <flux:profile
                        :initials="auth()->user()->initials()"
                        :avatar="auth()->user()->avatarUrl()"
                        icon-trailing="chevron-down"
                    />

                    @include('partials.user-menu')
                </flux:dropdown>
            @else
                <flux:button variant="primary" size="sm" :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:button>
            @endauth
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
