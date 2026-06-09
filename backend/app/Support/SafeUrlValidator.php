<?php

declare(strict_types=1);

namespace App\Support;

final class SafeUrlValidator
{
    private const BLOCKED_HOSTNAMES = [
        'localhost',
        'localhost.localdomain',
        'metadata.google.internal',
    ];

    public static function isSafe(string $url): bool
    {
        return self::validate($url, requireResolvableDns: false);
    }

    public static function isSafeForRequest(string $url): bool
    {
        return self::validate($url, requireResolvableDns: true);
    }

    private static function validate(string $url, bool $requireResolvableDns): bool
    {
        $parts = parse_url($url);

        if ($parts === false) {
            return false;
        }

        $scheme = strtolower($parts['scheme'] ?? '');
        if (! in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        $host = strtolower($parts['host'] ?? '');
        if ($host === '' || in_array($host, self::BLOCKED_HOSTNAMES, true)) {
            return false;
        }

        if (str_ends_with($host, '.localhost') || str_ends_with($host, '.local')) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return self::isPublicIp($host);
        }

        $resolvedIps = self::resolveHost($host);

        if ($resolvedIps === []) {
            return ! $requireResolvableDns && self::isPlausiblePublicHostname($host);
        }

        foreach ($resolvedIps as $ip) {
            if (! self::isPublicIp($ip)) {
                return false;
            }
        }

        return true;
    }

    private static function isPlausiblePublicHostname(string $host): bool
    {
        return str_contains($host, '.') && ! str_ends_with($host, '.');
    }

    /** @return list<string> */
    private static function resolveHost(string $host): array
    {
        set_error_handler(static fn () => true);
        $records = dns_get_record($host, DNS_A | DNS_AAAA);
        restore_error_handler();

        if ($records === false || $records === []) {
            return [];
        }

        $ips = [];
        foreach ($records as $record) {
            if (isset($record['ip'])) {
                $ips[] = $record['ip'];
            }
            if (isset($record['ipv6'])) {
                $ips[] = $record['ipv6'];
            }
        }

        return array_values(array_unique($ips));
    }

    private static function isPublicIp(string $value): bool
    {
        return (bool) filter_var(
            $value,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE,
        );
    }
}
