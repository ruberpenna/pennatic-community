<?php

namespace App\Support;

use Mews\Purifier\Facades\Purifier;

class PostContent
{
    /**
     * Sanitize editor HTML before it is stored and later rendered unescaped.
     */
    public static function clean(string $html): string
    {
        return trim(Purifier::clean($html, 'post'));
    }

    /**
     * Whether the HTML has any visible text or images.
     */
    public static function isBlank(string $html): bool
    {
        return trim(html_entity_decode(strip_tags($html, '<img>'), ENT_QUOTES | ENT_HTML5), " \t\n\r\0\x0B\u{00A0}") === '';
    }
}
