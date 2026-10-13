<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    /** @use HasFactory<\Database\Factories\MessageFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'recipient_id',
        'title',
        'content',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'sender_deleted_at' => 'datetime',
            'recipient_deleted_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<Message>  $query
     */
    public function scopeInboxOf(Builder $query, User $user): void
    {
        $query->where('recipient_id', $user->id)->whereNull('recipient_deleted_at');
    }

    /**
     * @param  Builder<Message>  $query
     */
    public function scopeSentBy(Builder $query, User $user): void
    {
        $query->where('sender_id', $user->id)->whereNull('sender_deleted_at');
    }

    /**
     * Whether the user can still see this message in their inbox or sent box.
     */
    public function isVisibleTo(User $user): bool
    {
        return ($this->recipient_id === $user->id && $this->recipient_deleted_at === null)
            || ($this->sender_id === $user->id && $this->sender_deleted_at === null);
    }

    public function markReadBy(User $user): void
    {
        if ($this->recipient_id === $user->id && $this->read_at === null) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }

    /**
     * Hide the message from the user's side; the row is removed once both sides deleted it.
     */
    public function deleteFor(User $user): void
    {
        if ($this->recipient_id === $user->id) {
            $this->recipient_deleted_at = now();
        }

        if ($this->sender_id === $user->id) {
            $this->sender_deleted_at = now();
        }

        if ($this->recipient_deleted_at && $this->sender_deleted_at) {
            $this->delete();
        } else {
            $this->save();
        }
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id')->withTrashed();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id')->withTrashed();
    }
}
