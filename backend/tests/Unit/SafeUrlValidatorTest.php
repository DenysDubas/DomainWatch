<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\SafeUrlValidator;
use PHPUnit\Framework\TestCase;

class SafeUrlValidatorTest extends TestCase
{
    public function test_blocks_loopback_addresses(): void
    {
        $this->assertFalse(SafeUrlValidator::isSafe('http://127.0.0.1'));
        $this->assertFalse(SafeUrlValidator::isSafe('http://localhost'));
    }

    public function test_blocks_private_networks(): void
    {
        $this->assertFalse(SafeUrlValidator::isSafe('http://10.0.0.1'));
        $this->assertFalse(SafeUrlValidator::isSafe('http://192.168.1.1'));
    }

    public function test_allows_public_https_urls(): void
    {
        $this->assertTrue(SafeUrlValidator::isSafe('https://93.184.216.34'));
        $this->assertTrue(SafeUrlValidator::isSafe('https://docs.example.org'));
    }

    public function test_blocks_non_http_schemes(): void
    {
        $this->assertFalse(SafeUrlValidator::isSafe('ftp://example.com'));
        $this->assertFalse(SafeUrlValidator::isSafe('file:///etc/passwd'));
    }
}
