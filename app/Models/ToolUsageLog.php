<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToolUsageLog extends Model
{
    protected $fillable = [
        'user_id',
        'tool_id',
        'processing_job_id',
        'usage_date',
        'ip_address',
        'session_id',
        'idempotency_key',
        'plan_tier',
        'status',
        'input_bytes',
        'output_bytes',
        'processing_time_ms',
    ];

    protected function casts(): array
    {
        return [
            'usage_date' => 'date:Y-m-d',
            'input_bytes' => 'integer',
            'output_bytes' => 'integer',
            'processing_time_ms' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function processingJob(): BelongsTo
    {
        return $this->belongsTo(ProcessingJob::class);
    }
}

