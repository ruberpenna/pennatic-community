<?php

namespace App\Livewire\Concerns;

/**
 * For Livewire actions that write (comments, votes): guests go to login,
 * members who have not verified their email go to the verification page.
 */
trait RequiresVerifiedMember
{
    protected function ensureVerifiedMember(): bool
    {
        $user = auth()->user();

        if (! $user) {
            $this->redirectRoute('login', navigate: true);

            return false;
        }

        if (! $user->hasVerifiedEmail()) {
            $this->redirectRoute('verification.notice', navigate: true);

            return false;
        }

        return true;
    }
}
