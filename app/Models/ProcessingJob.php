<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProcessingJob extends Model
{
    protected $fillable = [
        'job_uuid',
        'user_id',
        'tool_id',
        'ip_address',
        'status',
        'progress',
        'options',
        'result_metadata',
        'error_message',
        'processing_time_ms',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'result_metadata' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
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

    public function uploadedFiles(): HasMany
    {
        return $this->hasMany(UploadedFileRecord::class, 'processing_job_id');
    }

    public function processedFile(): HasOne
    {
        return $this->hasOne(ProcessedFile::class, 'processing_job_id');
    }
}

