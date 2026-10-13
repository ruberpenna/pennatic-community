<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        {{-- collapsible: on desktop the sidebar can shrink to an icon rail (names appear as tooltips); Flux stores the state. --}}
        <flux:sidebar sticky collapsible class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <flux:sidebar.brand :href="route('home')" :name="config('app.name')" wire:navigate>
                    <x-slot name="logo" class="size-8 rounded-md bg-accent-content text-accent-foreground">
                        <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
                    </x-slot>
                </flux:sidebar.brand>

                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.item icon="home" :href="route('home')" :current="request()->routeIs('home')" wire:navigate>{{ __('Home') }}</flux:sidebar.item>
                <flux:sidebar.item icon="megaphone" :href="route('notices.index')" :current="request()->routeIs('notices.*')" wire:navigate>{{ __('Notices') }}</flux:sidebar.item>

                {{-- Plain headings instead of sidebar.group: Flux hides whole groups in the collapsed rail, but board icons should stay. --}}
                @foreach ($boardFolders as $folder)
                    @continue($folder->boards->isEmpty())

                    @include('partials.sidebar-heading', ['heading' => $folder->name])

                    @foreach ($folder->boards as $board)
                        <flux:sidebar.item
                            icon="chat-bubble-left-right"
                            :href="route('boards.show', $board)"
                            :current="$currentBoardId === $board->id"
                            wire:navigate
                        >{{ $board->name }}</flux:sidebar.item>
                    @endforeach
                @endforeach

                @auth
                    @include('partials.sidebar-heading', ['heading' => __('My space')])

                    <flux:sidebar.item
                        icon="envelope"
                        :href="route('messages.index')"
                        :current="request()->routeIs('messages.*')"
                        :badge="$unreadMessages ?: null"
                        :icon-dot="$unreadMessages > 0"
                        wire:navigate
                    >{{ __('Messages') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="pencil" :href="route('memos.index')" :current="request()->routeIs('memos.*')" wire:navigate>{{ __('Memos') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="calendar-days" :href="route('attendance')" :current="request()->routeIs('attendance')" wire:navigate>{{ __('Attendance') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="lifebuoy" :href="route('contact')" :current="request()->routeIs('contact')" wire:navigate>{{ __('Contact the admin') }}</flux:sidebar.item>
                @endauth
            </flux:sidebar.nav>

            <flux:sidebar.spacer />

            <x-theme-toggle class="max-lg:hidden in-data-flux-sidebar-collapsed-desktop:hidden" />
            <x-theme-menu class="hidden in-data-flux-sidebar-collapsed-desktop:block" />

            @auth
                <flux:dropdown position="top" align="start" class="max-lg:hidden">
                    <flux:sidebar.profile
                        :name="auth()->user()->name"
                        :initials="auth()->user()->initials()"
                        :avatar="auth()->user()->avatarUrl()"
                        icon-trailing="chevrons-up-down"
                    />

                    @include('partials.user-menu')
                </flux:dropdown>
            @else
                <div class="flex flex-col gap-2 in-data-flux-sidebar-collapsed-desktop:hidden">
                    <flux:button variant="primary" size="sm" :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:button>
                    <flux:button size="sm" :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:button>
                </div>
                <div class="hidden in-data-flux-sidebar-collapsed-desktop:block">
                    <flux:sidebar.item icon="arrow-right-end-on-rectangle" :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="user-plus" :href="route('register')" wire:navigate>{{ __('Sign up') }}</flux:sidebar.item>
                </div>
            @endauth

            <x-sidebar-toggle />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <x-theme-toggle class="me-2" />

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
