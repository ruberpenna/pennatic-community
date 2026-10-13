<?php

namespace App\Rules;

use App\Models\User;
use App\Support\EmailAddress;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Fails when another account (including withdrawn ones) already uses the same mailbox,
 * e.g. "a.b+x@gmail.com" when "ab@gmail.com" is registered.
 */
class UniqueMailbox implements ValidationRule
{
    public function __construct(private ?int $ignoreUserId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $taken = User::withTrashed()
            ->where('email_canonical', EmailAddress::canonical((string) $value))
            ->when($this->ignoreUserId, fn ($query) => $query->whereKeyNot($this->ignoreUserId))
            ->exists();

        if ($taken) {
            $fail(__('This email address is already registered.'));
        }
    }
}
