<?php

declare(strict_types=1);

namespace App\Enums;

enum HttpMethod: string
{
    case Get = 'GET';
    case Head = 'HEAD';
}
