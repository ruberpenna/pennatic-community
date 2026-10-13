{{-- Admin panel top bar: link back to the community site. --}}
<x-filament::button
    tag="a"
    :href="route('home')"
    icon="heroicon-o-home"
    color="gray"
    size="sm"
    outlined
>
    {{ __('Site home') }}
</x-filament::button>
