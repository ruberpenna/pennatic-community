<?php

namespace App\Support;

/**
 * Canonical form of an email address, used to stop one mailbox from registering several accounts.
 *
 * - lower-cased
 * - "+tag" removed for providers that deliver user+tag@ to user@
 * - dots in the local part removed for Gmail, which ignores them
 */
class EmailAddress
{
    /** Providers that support "+" sub-addressing. */
    private const PLUS_ADDRESSING = [
        'gmail.com', 'googlemail.com',
        'outlook.com', 'hotmail.com', 'live.com', 'msn.com',
        'icloud.com', 'me.com', 'mac.com',
        'proton.me', 'protonmail.com', 'pm.me',
        'fastmail.com', 'kakao.com',
    ];

    public static function canonical(string $email): string
    {
        $email = mb_strtolower(trim($email));

        if (! str_contains($email, '@')) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);

        if ($domain === 'googlemail.com') {
            $domain = 'gmail.com';
        }

        if (in_array($domain, self::PLUS_ADDRESSING, true)) {
            $local = explode('+', $local, 2)[0];
        }

        if ($domain === 'gmail.com') {
            $local = str_replace('.', '', $local);
        }

        return $local.'@'.$domain;
    }
}
