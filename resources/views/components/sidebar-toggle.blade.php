{{--
    Round button on the sidebar's right edge (desktop only) that collapses it to the icon rail and back.
    Placed inside <flux:sidebar> so the collapsed-state variants apply; position:fixed keeps it outside
    the sidebar's scroll container. Flux listens for "flux-sidebar-toggle" and persists the state.
--}}
<button
    type="button"
    x-data
    x-on:click="$el.dispatchEvent(new CustomEvent('flux-sidebar-toggle', { bubbles: true }))"
    class="fixed top-1/2 left-[calc(16rem-0.875rem)] z-30 flex size-7 -translate-y-1/2 items-center justify-center rounded-full border border-zinc-200 bg-white text-zinc-500 shadow-sm transition-[left] hover:text-zinc-800 max-lg:hidden in-data-flux-sidebar-collapsed-desktop:left-[calc(3.5rem-0.875rem)] dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:text-white"
>
    <span class="contents in-data-flux-sidebar-collapsed-desktop:hidden">
        <flux:icon.chevron-left variant="micro" />
        <span class="sr-only">{{ __('Collapse menu') }}</span>
    </span>
    <span class="hidden in-data-flux-sidebar-collapsed-desktop:contents">
        <flux:icon.chevron-right variant="micro" />
        <span class="sr-only">{{ __('Expand menu') }}</span>
    </span>
</button>
