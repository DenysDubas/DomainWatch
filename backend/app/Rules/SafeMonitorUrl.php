<?php

declare(strict_types=1);

namespace App\Rules;

use App\Support\SafeUrlValidator;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class SafeMonitorUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! filter_var($value, FILTER_VALIDATE_URL)) {
            $fail('The :attribute must be a valid URL.');

            return;
        }

        if (! SafeUrlValidator::isSafe($value)) {
            $fail('The :attribute must be a public HTTP/HTTPS URL and cannot target private or internal networks.');
        }
    }
}
