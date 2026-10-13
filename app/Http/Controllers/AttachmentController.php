<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function __invoke(Attachment $attachment): StreamedResponse
    {
        // Files of deleted posts are no longer downloadable.
        abort_if($attachment->post()->doesntExist(), 404);
        $attachment->post->board->ensureVisibleTo(auth()->user());
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        $attachment->newQuery()->whereKey($attachment->id)->increment('download_count');

        return $attachment->download();
    }
}
