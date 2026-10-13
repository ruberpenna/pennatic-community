@props([
    'model',
    'value' => '',
    'label' => null,
])

@php($inputId = 'editor-'.md5($model))

{{-- Trix keeps its own DOM, so Livewire must not morph it; changes are pushed to the model without a request. --}}
<flux:field>
    @if ($label)
        <flux:label>{{ $label }}</flux:label>
    @endif

    <div wire:ignore x-data x-on:trix-change="$wire.set('{{ $model }}', $event.target.value, false)">
        <input id="{{ $inputId }}" type="hidden" value="{{ $value }}">
        <trix-editor
            input="{{ $inputId }}"
            class="post-content min-h-72 rounded-lg border border-zinc-200 bg-white px-3 py-2 dark:border-white/10 dark:bg-white/10"
        ></trix-editor>
    </div>

    <flux:error :name="$model" />
</flux:field>
