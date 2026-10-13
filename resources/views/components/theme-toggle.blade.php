{{-- Light / dark / system switch; Flux stores the choice in localStorage ("flux.appearance"). --}}
<flux:radio.group x-data variant="segmented" size="sm" x-model="$flux.appearance" {{ $attributes->merge(['aria-label' => __('Theme')]) }}>
    <flux:radio value="light" icon="sun" :aria-label="__('Light')" :title="__('Light')" />
    <flux:radio value="dark" icon="moon" :aria-label="__('Dark')" :title="__('Dark')" />
    <flux:radio value="system" icon="computer-desktop" :aria-label="__('System')" :title="__('System')" />
</flux:radio.group>
