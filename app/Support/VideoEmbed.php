<?php

namespace App\Support;

/**
 * Turns YouTube and CHZZK links in sanitized post HTML into embedded players at render time.
 *
 * Stored content never contains iframes: the purifier strips them, and the player markup is built
 * here only from IDs that match strict patterns, so arbitrary embeds cannot be injected.
 */
class VideoEmbed
{
    private const PATTERNS = [
        // youtube.com/watch?v=ID, /shorts/ID, /embed/ID, /live/ID and youtu.be/ID
        'youtube' => '~^(?:https?://)?(?:www\.|m\.)?(?:youtube\.com/(?:watch\?(?:[^#]*?&(?:amp;)?)?v=|shorts/|embed/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})(?![A-Za-z0-9_-])~i',
        'chzzk-video' => '~^(?:https?://)?chzzk\.naver\.com/(?:embed/)?video/(\d{1,12})(?!\d)~i',
        'chzzk-clip' => '~^(?:https?://)?chzzk\.naver\.com/(?:clips|embed/clip)/([A-Za-z0-9]{4,40})(?![A-Za-z0-9])~i',
    ];

    private const URL_IN_TEXT = '~https?://(?:www\.|m\.)?(?:youtube\.com|youtu\.be|chzzk\.naver\.com)/[^\s<"\']+~i';

    public static function render(string $html): string
    {
        // Split into tags and text so URLs inside attributes are never touched.
        $tokens = preg_split('/(<[^>]*>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $out = '';
        $skipUntilAnchorClose = false;
        $anchorDepth = 0;

        foreach ($tokens as $token) {
            $isTag = str_starts_with($token, '<');

            if ($skipUntilAnchorClose) {
                if ($isTag && preg_match('~^</a\s*>~i', $token)) {
                    $skipUntilAnchorClose = false;
                }

                continue;
            }

            if ($isTag) {
                if (preg_match('~^<a\s[^>]*href="([^"]+)"~i', $token, $m) && ($embed = self::embedFor(html_entity_decode($m[1])))) {
                    // A link to a video becomes the player; drop the link text.
                    $out .= $embed;
                    $skipUntilAnchorClose = true;

                    continue;
                }

                if (preg_match('~^<a[\s>]~i', $token)) {
                    $anchorDepth++;
                } elseif (preg_match('~^</a\s*>~i', $token)) {
                    $anchorDepth = max(0, $anchorDepth - 1);
                }

                $out .= $token;

                continue;
            }

            // Plain text URLs (outside of links) become players too.
            $out .= $anchorDepth > 0 ? $token : preg_replace_callback(
                self::URL_IN_TEXT,
                fn (array $m) => self::embedFor(html_entity_decode($m[0])) ?? $m[0],
                $token,
            );
        }

        return $out;
    }

    public static function embedFor(string $url): ?string
    {
        $url = trim($url);

        if (preg_match(self::PATTERNS['youtube'], $url, $m)) {
            return self::player('https://www.youtube-nocookie.com/embed/'.$m[1], 'YouTube');
        }

        if (preg_match(self::PATTERNS['chzzk-video'], $url, $m)) {
            return self::player('https://chzzk.naver.com/embed/video/'.$m[1], 'CHZZK');
        }

        if (preg_match(self::PATTERNS['chzzk-clip'], $url, $m)) {
            return self::player('https://chzzk.naver.com/embed/clip/'.$m[1], 'CHZZK');
        }

        return null;
    }

    private static function player(string $src, string $title): string
    {
        return '<div class="video-embed"><iframe src="'.e($src).'" title="'.e($title).'"'
            .' allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"'
            .' allowfullscreen loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe></div>';
    }
}
