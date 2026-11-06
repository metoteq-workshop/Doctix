<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UploadedFileRecord extends Model
{
    protected $table = 'uploaded_files';

    protected $fillable = [
        'file_uuid',
        'user_id',
        'tool_id',
        'processing_job_id',
        'original_name',
        'stored_name',
        'disk',
        'file_path',
        'mime_type',
        'extension',
        'file_size',
        'checksum',
        'virus_scan_status',
        'expires_at',
        'is_deleted',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
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
}

