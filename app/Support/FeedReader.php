<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use SimpleXMLElement;

/**
 * Minimal RSS 2.0 / Atom reader returning plain-text items.
 */
class FeedReader
{
    /**
     * @return list<array{guid: string, title: string, link: string, summary: string, published_at: ?Carbon}>
     */
    public static function parse(string $xml): array
    {
        $previous = libxml_use_internal_errors(true);
        // LIBXML_NONET: never fetch external entities/DTDs referenced by the feed.
        $feed = simplexml_load_string($xml, SimpleXMLElement::class, LIBXML_NOCDATA | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($feed === false) {
            throw new \RuntimeException('Not a valid RSS/Atom document.');
        }

        $items = [];

        if (isset($feed->channel)) {
            foreach ($feed->channel->item as $item) {
                $items[] = self::item(
                    guid: (string) ($item->guid ?: $item->link),
                    title: (string) $item->title,
                    link: (string) $item->link,
                    summary: (string) $item->description,
                    date: (string) $item->pubDate,
                );
            }
        } elseif ($feed->getName() === 'feed') {
            foreach ($feed->entry as $entry) {
                $link = '';
                foreach ($entry->link as $candidate) {
                    if (in_array((string) $candidate['rel'], ['', 'alternate'], true)) {
                        $link = (string) $candidate['href'];
                        break;
                    }
                }

                $items[] = self::item(
                    guid: (string) ($entry->id ?: $link),
                    title: (string) $entry->title,
                    link: $link,
                    summary: (string) ($entry->summary ?: $entry->content),
                    date: (string) ($entry->published ?: $entry->updated),
                );
            }
        } else {
            throw new \RuntimeException('Not a valid RSS/Atom document.');
        }

        return array_values(array_filter($items, fn (array $item) => $item['title'] !== '' && $item['link'] !== ''));
    }

    /**
     * @return array{guid: string, title: string, link: string, summary: string, published_at: ?Carbon}
     */
    private static function item(string $guid, string $title, string $link, string $summary, string $date): array
    {
        $link = trim($link);

        return [
            'guid' => trim($guid) ?: $link,
            'title' => self::text($title),
            // Only web links; anything else (javascript:, data:) is dropped.
            'link' => preg_match('~^https?://~i', $link) ? $link : '',
            'summary' => self::text($summary),
            'published_at' => self::date($date),
        ];
    }

    private static function text(string $html): string
    {
        // strip_tags keeps the text inside <script>/<style>; drop those blocks entirely.
        $html = preg_replace('~<(script|style)\b[^>]*>.*?</\1\s*>~is', '', $html);
        $text = html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], ' ', $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    private static function date(string $value): ?Carbon
    {
        try {
            return $value !== '' ? Carbon::parse($value) : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
