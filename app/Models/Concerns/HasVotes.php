<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * Up/down votes stored in a votes() HasMany relation with (user_id, value).
 */
trait HasVotes
{
    /**
     * Voting the same way again cancels the vote; voting the other way switches it.
     */
    public function toggleVote(User $user, int $value): void
    {
        $value = $value > 0 ? 1 : -1;
        $existing = $this->votes()->where('user_id', $user->id)->first();

        if ($existing?->value === $value) {
            $existing->delete();
        } elseif ($existing) {
            $existing->update(['value' => $value]);
        } else {
            try {
                $this->votes()->create(['user_id' => $user->id, 'value' => $value]);
            } catch (UniqueConstraintViolationException) {
                // A concurrent request (double click) already recorded the vote.
            }
        }
    }

    public function voteOf(?User $user): int
    {
        return $user ? (int) $this->votes()->where('user_id', $user->id)->value('value') : 0;
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeWithVoteCounts(Builder $query): void
    {
        $query->withCount([
            'votes as upvotes_count' => fn (Builder $q) => $q->where('value', 1),
            'votes as downvotes_count' => fn (Builder $q) => $q->where('value', -1),
        ]);
    }
}
