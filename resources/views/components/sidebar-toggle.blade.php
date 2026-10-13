{{-- Desktop-only button on the sidebar's edge that hides/shows it; the state lives on <html> and in localStorage. --}}
<button
    type="button"
    x-data="{ hidden: document.documentElement.classList.contains('sidebar-hidden') }"
    x-on:click="
        hidden = ! hidden;
        document.documentElement.classList.toggle('sidebar-hidden', hidden);
        try { localStorage.setItem('penna.sidebar', hidden ? 'hidden' : 'shown') } catch (e) {}
    "
    x-bind:aria-label="hidden ? @js(__('Show menu')) : @js(__('Hide menu'))"
    x-bind:title="hidden ? @js(__('Show menu')) : @js(__('Hide menu'))"
    x-bind:aria-expanded="(! hidden).toString()"
    class="sidebar-toggle fixed top-1/2 z-30 hidden size-7 -translate-y-1/2 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-500 shadow-sm transition-[left] hover:text-zinc-800 lg:flex dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:text-white"
>
    <flux:icon.chevron-left variant="micro" x-show="! hidden" />
    <flux:icon.chevron-right variant="micro" x-show="hidden" x-cloak />
</button>
