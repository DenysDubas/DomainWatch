<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\PingResultPayload;
use App\Enums\CheckStatus;
use App\Events\DomainStatusChanged;
use App\Models\Domain;
use App\Repositories\CheckLogRepo;
use App\Support\PingErrorFormatter;
use App\Support\SafeUrlValidator;
use Illuminate\Support\Facades\Http;
use Throwable;

class PingService
{
    public function __construct(private readonly CheckLogRepo $logRepo) {}

    public function execute(Domain $domain): PingResultPayload
    {
        $domain->markCheckingStarted();

        $previousStatus = $domain->last_status;
        $result         = $this->performRequest($domain);

        $this->logRepo->createForDomain($domain, [
            'status'        => $result->status,
            'response_code' => $result->responseCode,
            'response_time' => $result->responseTime,
            'error_message' => $result->errorMessage,
            'checked_at'    => now(),
        ]);

        $domain->markCheckingFinished($result->status, $result->responseCode);

        if ($previousStatus !== null && $previousStatus !== $result->status) {
            DomainStatusChanged::dispatch($domain, $result->status);
        }

        return $result;
    }

    private function performRequest(Domain $domain): PingResultPayload
    {
        $start = microtime(true);

        if (! SafeUrlValidator::isSafeForRequest($domain->url)) {
            return $this->downResult($start, 'URL targets a blocked or internal network.');
        }

        try {
            $method = strtolower($domain->method->value);
            $response = Http::timeout($domain->timeout)
                ->withOptions([
                    'allow_redirects' => false,
                    'http_errors'     => false,
                ])
                ->{$method}($domain->url);

            $elapsed = (microtime(true) - $start) * 1000;
            $code    = $response->status();
            $isUp    = $response->successful();

            return new PingResultPayload(
                status:       $isUp ? CheckStatus::Up : CheckStatus::Down,
                responseCode: $code,
                responseTime: round($elapsed, 2),
                errorMessage: $isUp ? null : PingErrorFormatter::fromHttpStatus($code),
            );
        } catch (Throwable $e) {
            return $this->downResult($start, PingErrorFormatter::fromException($e->getMessage()));
        }
    }

    private function downResult(float $start, string $message): PingResultPayload
    {
        $elapsed = (microtime(true) - $start) * 1000;

        return new PingResultPayload(
            status:       CheckStatus::Down,
            responseCode: null,
            responseTime: round($elapsed, 2),
            errorMessage: $message,
        );
    }
}
