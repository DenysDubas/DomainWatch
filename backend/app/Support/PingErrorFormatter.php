<?php

declare(strict_types=1);

namespace App\Support;

final class PingErrorFormatter
{
    public static function fromException(string $message): string
    {
        if (preg_match('/cURL error \d+:\s*(.+?)\s*(?:\(see https:|for https?:\/\/)/s', $message, $matches)) {
            return trim($matches[1]);
        }

        if (preg_match('/Could not resolve host:\s*(\S+)/', $message, $matches)) {
            return "Could not resolve host: {$matches[1]}";
        }

        if (str_contains($message, 'Connection timed out') || str_contains($message, 'Operation timed out')) {
            return 'Connection timed out';
        }

        if (str_contains($message, 'SSL') || str_contains($message, 'certificate')) {
            return 'SSL certificate error';
        }

        if (str_contains($message, 'Failed to connect')) {
            return 'Failed to connect to host';
        }

        return self::truncate($message);
    }

    public static function fromHttpStatus(int $code): string
    {
        return match (true) {
            $code === 404 => 'HTTP 404 Not Found',
            $code === 403 => 'HTTP 403 Forbidden',
            $code === 401 => 'HTTP 401 Unauthorized',
            $code >= 500 => "HTTP {$code} Server Error",
            $code >= 400 => "HTTP {$code} Client Error",
            default       => "HTTP {$code}",
        };
    }

    private static function truncate(string $message): string
    {
        return mb_strlen($message) > 120 ? mb_substr($message, 0, 117).'...' : $message;
    }
}
