<?php

namespace App\Support;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;

/**
 * Reads a hot-deal post page and returns the seller's link.
 *
 * Only sites whose page structure (and robots.txt) has been checked are listed, each with the
 * XPath of its "source" field. Sites that block automated access are not added.
 */
class ShopLink
{
    /**
     * @var array<string, array{label: string, xpath: string}>
     */
    public const PARSERS = [
        // bbs.ruliweb.com/market/board/1020 (hot deals): <div class="source_url">출처 : <a href="...">
        'ruliweb' => [
            'label' => 'Ruliweb (source field)',
            'xpath' => '//div[contains(concat(" ", normalize-space(@class), " "), " source_url ")]//a/@href',
        ],
    ];

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::PARSERS)->map(fn (array $parser) => __($parser['label']))->all();
    }

    public static function find(string $parser, string $pageUrl): ?string
    {
        if (! isset(self::PARSERS[$parser])) {
            return null;
        }

        $html = Http::timeout(15)
            ->withUserAgent('PennaNewsBot/1.0 (+'.config('app.url').')')
            ->get($pageUrl)
            ->throw()
            ->body();

        return self::extract($html, self::PARSERS[$parser]['xpath'], $pageUrl);
    }

    public static function extract(string $html, string $xpath, string $pageUrl): ?string
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        // The XML declaration makes libxml read the page as UTF-8.
        $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NONET | LIBXML_NOERROR);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $pageHost = parse_url($pageUrl, PHP_URL_HOST);

        foreach ((new DOMXPath($document))->query($xpath) ?: [] as $node) {
            $url = trim($node->nodeValue);

            // A real outside link only: http(s), and not back to the community site itself.
            if (preg_match('~^https?://~i', $url) && parse_url($url, PHP_URL_HOST) !== $pageHost) {
                return $url;
            }
        }

        return null;
    }
}
