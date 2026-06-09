<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\HttpMethod;

final readonly class DomainPayload
{
    public function __construct(
        public string     $url,
        public string     $name,
        public int        $checkInterval,
        public int        $timeout,
        public HttpMethod $method,
        public bool       $isActive,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            url:           $data['url'],
            name:          $data['name'],
            checkInterval: (int) $data['check_interval'],
            timeout:       (int) $data['timeout'],
            method:        HttpMethod::from($data['method']),
            isActive:      (bool) ($data['is_active'] ?? true),
        );
    }
}
