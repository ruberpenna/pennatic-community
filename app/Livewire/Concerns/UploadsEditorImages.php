<?php

namespace App\Livewire\Concerns;

use App\Support\EditorImage;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Lets the Trix editor upload an image (via $wire.upload('editorImage', ...)) and get its URL back.
 * The using component must also use Livewire\WithFileUploads.
 */
trait UploadsEditorImages
{
    /** Images a user may insert per 10 minutes. */
    public const EDITOR_IMAGES_PER_WINDOW = 60;

    public $editorImage = null;

    public function storeEditorImage(): string
    {
        $key = 'editor-image:'.auth()->id();

        if (RateLimiter::tooManyAttempts($key, self::EDITOR_IMAGES_PER_WINDOW)) {
            $this->reset('editorImage');

            throw ValidationException::withMessages([
                'editorImage' => __('Too many images. Try again in :minutes minutes.', ['minutes' => ceil(RateLimiter::availableIn($key) / 60)]),
            ]);
        }

        try {
            $this->validate(['editorImage' => EditorImage::rules()], attributes: ['editorImage' => __('Image')]);
        } catch (ValidationException $e) {
            $this->reset('editorImage');

            throw $e;
        }

        RateLimiter::hit($key, 600);

        $url = EditorImage::store($this->editorImage);
        $this->reset('editorImage');

        return $url;
    }
}
