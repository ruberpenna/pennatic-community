@props([
    'model',
    'value' => '',
    'label' => null,
    'images' => false, // allow inserting images; the Livewire component must use UploadsEditorImages
])

@php($inputId = 'editor-'.md5($model))

{{--
    Trix keeps its own DOM, so Livewire must not morph it; changes are pushed to the model without a request.
    Images are uploaded through Livewire ($wire.upload) and Trix gets back the stored URL.
--}}
<flux:field>
    @if ($label)
        <flux:label>{{ $label }}</flux:label>
    @endif

    <div
        wire:ignore
        x-data
        x-on:trix-change="$wire.set('{{ $model }}', $event.target.value, false)"
        @if ($images)
            data-editor-images
            data-max-bytes="{{ \App\Support\EditorImage::MAX_KB * 1024 }}"
            data-types="{{ implode(',', \App\Support\EditorImage::TYPES) }}"
            data-too-large="{{ __('Images can be up to :size MB.', ['size' => \App\Support\EditorImage::MAX_KB / 1024]) }}"
            data-wrong-type="{{ __('Only JPG, PNG, GIF and WebP images can be inserted.') }}"
            data-upload-failed="{{ __('The image could not be uploaded.') }}"
            x-on:trix-attachment-add="window.pennaUploadEditorImage($event, $wire)"
        @endif
    >
        <input id="{{ $inputId }}" type="hidden" value="{{ $value }}">
        <trix-editor
            input="{{ $inputId }}"
            class="post-content min-h-72 rounded-lg border border-zinc-200 bg-white px-3 py-2 dark:border-white/10 dark:bg-white/10"
        ></trix-editor>
    </div>

    @if ($images)
        <flux:description>
            {{ __('Images: attach with the clip button, drag or paste (JPG, PNG, GIF, WebP, up to :size MB each).', ['size' => \App\Support\EditorImage::MAX_KB / 1024]) }}
            {{ __('Videos: paste a YouTube or CHZZK link and it is shown as a player.') }}
        </flux:description>
    @endif

    <flux:error :name="$model" />
</flux:field>
