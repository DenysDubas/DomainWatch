<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CheckStatus;
use App\Enums\HttpMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'name',
        'check_interval',
        'timeout',
        'method',
        'is_active',
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'last_checked_at'    => 'datetime',
        'checking_started_at' => 'datetime',
        'check_interval'     => 'integer',
        'timeout'            => 'integer',
        'last_response_code' => 'integer',
        'method'             => HttpMethod::class,
        'last_status'        => CheckStatus::class,
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function checkLogs(): HasMany
    {
        return $this->hasMany(CheckLog::class);
    }

    public function markCheckingStarted(): void
    {
        $this->forceFill(['checking_started_at' => now()])->save();
    }

    public function markCheckingFinished(CheckStatus $status, ?int $responseCode): void
    {
        $this->forceFill([
            'last_checked_at'     => now(),
            'checking_started_at' => null,
            'last_status'         => $status,
            'last_response_code'  => $responseCode,
        ])->save();
    }
}
