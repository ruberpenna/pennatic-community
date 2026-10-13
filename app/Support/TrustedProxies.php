<?php

namespace App\Support;

/**
 * Proxies allowed to set X-Forwarded-* headers.
 *
 * Visitors reach the site through Cloudflare, then Nginx Proxy Manager on the LAN. Trusting both
 * lets Laravel read the visitor's real IP from X-Forwarded-For (login and signup rate limits depend
 * on it), while a visitor connecting any other way cannot spoof it.
 *
 * Cloudflare ranges: https://www.cloudflare.com/ips/ (checked 2026-10-13). They change rarely;
 * update this list if Cloudflare publishes new ranges.
 */
class TrustedProxies
{
    public const NGINX_PROXY_MANAGER = '192.168.0.33';

    public const CLOUDFLARE = [
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15',
        '104.16.0.0/13',
        '104.24.0.0/14',
        '172.64.0.0/13',
        '131.0.72.0/22',
        '2400:cb00::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2405:b500::/32',
        '2405:8100::/32',
        '2a06:98c0::/29',
        '2c0f:f248::/32',
    ];

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [self::NGINX_PROXY_MANAGER, ...self::CLOUDFLARE];
    }
}
