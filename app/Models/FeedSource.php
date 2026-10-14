<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeedSource extends Model
{
    /** @use HasFactory<\Database\Factories\FeedSourceFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'board_id',
        'name',
        'subject',
        'url',
        'is_active',
        'max_items',
        'fetch_interval_hours',
    ];

    /** Choices for fetch_interval_hours; null means once a day at 07:00. */
    public const INTERVALS = [1, 2, 3, 6, 12];

    /**
     * @param  Builder<FeedSource>  $query
     */
    public function scopeDaily(Builder $query): void
    {
        $query->where('is_active', true)->whereNull('fetch_interval_hours');
    }

    /**
     * @param  Builder<FeedSource>  $query
     */
    public function scopeFrequent(Builder $query): void
    {
        $query->where('is_active', true)->whereNotNull('fetch_interval_hours');
    }

    /**
     * Whether a frequent source's interval has passed. A few minutes of slack keeps an hourly
     * source from being skipped when the previous run finished slightly after the hour.
     */
    public function isDue(): bool
    {
        return $this->last_fetched_at === null
            || $this->last_fetched_at->lte(now()->subHours($this->fetch_interval_hours ?? 24)->addMinutes(5));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'max_items' => 'integer',
            'fetch_interval_hours' => 'integer',
            'last_fetched_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Board, $this>
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    /**
     * @return HasMany<FeedItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(FeedItem::class);
    }
}
