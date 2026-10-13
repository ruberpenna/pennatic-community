<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Attachment extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'original_name',
        'path',
        'mime_type',
        'size',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'download_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(fn (Attachment $attachment) => Storage::disk('local')->delete($attachment->path));
    }

    public function download(): StreamedResponse
    {
        return Storage::disk('local')->download($this->path, $this->original_name);
    }

    /**
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
