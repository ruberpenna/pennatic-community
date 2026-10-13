<?php

namespace App\Models;

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
        'subjects',
        'sort_order',
    ];

    public function menuIcon(): string
    {
        return array_key_exists((string) $this->icon, self::ICONS) ? $this->icon : self::DEFAULT_ICON;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subjects' => 'array',
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
