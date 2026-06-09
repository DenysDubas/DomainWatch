<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\HttpMethod;
use App\Rules\SafeMonitorUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url'            => ['required', 'url', 'max:2048', new SafeMonitorUrl],
            'name'           => ['required', 'string', 'max:100'],
            'check_interval' => ['required', 'integer', 'min:1', 'max:1440'],
            'timeout'        => ['required', 'integer', 'min:1', 'max:60'],
            'method'         => ['required', Rule::enum(HttpMethod::class)],
            'is_active'      => ['sometimes', 'boolean'],
        ];
    }
}
