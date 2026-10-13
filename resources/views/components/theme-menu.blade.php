{{-- Compact theme switch for the collapsed sidebar rail: one icon button opening light/dark/system. --}}
<flux:dropdown position="right" align="end" {{ $attributes }}>
    <flux:tooltip :content="__('Theme')" position="right">
        <flux:button variant="ghost" square :aria-label="__('Theme')">
            <flux:icon.sun variant="mini" x-show="! $flux.dark" />
            <flux:icon.moon variant="mini" x-show="$flux.dark" x-cloak />
        </flux:button>
    </flux:tooltip>

    <flux:menu>
        <flux:menu.radio.group x-data x-model="$flux.appearance">
            <flux:menu.radio value="light" icon="sun">{{ __('Light') }}</flux:menu.radio>
            <flux:menu.radio value="dark" icon="moon">{{ __('Dark') }}</flux:menu.radio>
            <flux:menu.radio value="system" icon="computer-desktop">{{ __('System') }}</flux:menu.radio>
        </flux:menu.radio.group>
    </flux:menu>
</flux:dropdown>
