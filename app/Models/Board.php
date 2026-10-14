<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Board extends Model
{
    /** @use HasFactory<\Database\Factories\BoardFactory> */
    use HasFactory;

    public const DEFAULT_ICON = 'chat-bubble-left-right';

    /**
     * Heroicon names admins can pick for a board's menu icon => translation key of its label.
     *
     * @var array<string, string>
     */
    public const ICONS = [
        'chat-bubble-left-right' => 'Talk',
        'question-mark-circle' => 'Questions',
        'light-bulb' => 'Tips',
        'book-open' => 'Info',
        'newspaper' => 'News',
        'photo' => 'Photos',
        'film' => 'Videos',
        'musical-note' => 'Music',
        'puzzle-piece' => 'Games',
        'code-bracket' => 'Development',
        'shopping-bag' => 'Market',
        'heart' => 'Hobbies',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'board_folder_id',
        'name',
        'icon',
        'is_private',
        'subjects',
        'sort_order',
    ];

    public const PRIVATE_ICON = 'lock-closed';

    protected static function booted(): void
    {
        // Posts restrict board deletion at the database level. Admins may delete a board once it has
        // no visible posts; soft-deleted leftovers (with their comments and files) go with it.
        static::deleting(fn (Board $board) => $board->purgeDeletedPosts());
    }

    public function purgeDeletedPosts(): void
    {
        // forceDelete per post so attachment files are removed too (see Post::booted).
        $this->posts()->onlyTrashed()->get()->each->forceDelete();
    }

    public function menuIcon(): string
    {
        if ($this->is_private) {
            return self::PRIVATE_ICON;
        }

        return array_key_exists((string) $this->icon, self::ICONS) ? $this->icon : self::DEFAULT_ICON;
    }

    /**
     * Private boards (and their posts, comments and files) exist only for admins.
     */
    public function isVisibleTo(?User $user): bool
    {
        return ! $this->is_private || (bool) $user?->isAdmin();
    }

    /**
     * Abort with 404 (not 403) so non-admins cannot tell that a private board exists.
     */
    public function ensureVisibleTo(?User $user): void
    {
        abort_unless($this->isVisibleTo($user), 404);
    }

    /**
     * @param  Builder<Board>  $query
     */
    public function scopeVisibleTo(Builder $query, ?User $user): void
    {
        if (! $user?->isAdmin()) {
            $query->where('is_private', false);
        }
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subjects' => 'array',
            'is_private' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<BoardFolder, $this>
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(BoardFolder::class, 'board_folder_id');
    }

    /**
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
