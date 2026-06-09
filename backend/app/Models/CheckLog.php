<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CheckStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'response_code',
        'response_time',
        'error_message',
        'checked_at',
    ];

    protected $casts = [
        'status'        => CheckStatus::class,
        'response_code' => 'integer',
        'response_time' => 'float',
        'checked_at'    => 'datetime',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }
}
