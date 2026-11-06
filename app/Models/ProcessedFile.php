<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessedFile extends Model
{
    protected $fillable = [
        'download_token',
        'user_id',
        'tool_id',
        'processing_job_id',
        'file_name',
        'disk',
        'file_path',
        'mime_type',
        'extension',
        'file_size',
        'download_count',
        'expires_at',
        'is_deleted',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'download_count' => 'integer',
            'expires_at' => 'datetime',
            'is_deleted' => 'boolean',
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

    public function formattedSize(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}

