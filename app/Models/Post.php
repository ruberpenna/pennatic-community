<?php

namespace App\Models;

use App\Models\Concerns\HasVotes;
use App\Support\Like;
use Illuminate\Contracts\Session\Session;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;

class Post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory, HasVotes, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'board_id',
        'subject',
        'title',
        'content',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'view_count' => 'integer',
            'is_pinned' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // Attachment rows go with the post via the foreign key; remove their files too.
        static::forceDeleting(fn (Post $post) => $post->attachments->each->delete());
    }

    /**
     * Store an uploaded file privately and attach it to this post.
     */
    public function addAttachment(UploadedFile $file): Attachment
    {
        return $this->attachments()->create([
            'original_name' => $file->getClientOriginalName(),
            'path' => $file->store('attachments', 'local'),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    /**
     * Count one view per post per session.
     */
    public function recordView(Session $session): void
    {
        $key = 'viewed_posts.'.$this->id;

        if ($session->has($key)) {
            return;
        }

        $session->put($key, true);
        static::whereKey($this->id)->toBase()->increment('view_count');
        $this->view_count++;
    }

    /**
     * @param  Builder<Post>  $query
     */
    public function scopeSearch(Builder $query, string $field, string $term): void
    {
        match ($field) {
            'content' => Like::where($query, 'content', $term),
            'author' => $query->whereHas('user', fn (Builder $q) => Like::where($q->withTrashed(), 'name', $term)),
            'title_content' => $query->where(fn (Builder $q) => Like::where(Like::where($q, 'title', $term), 'content', $term, 'or')),
            default => Like::where($query, 'title', $term),
        };
    }

    /**
     * @return BelongsTo<Board, $this>
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * @return HasMany<Attachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * @return HasMany<PostVote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(PostVote::class);
    }
}
