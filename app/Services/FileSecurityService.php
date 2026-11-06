<?php

namespace App\Services;

use App\Models\ProcessedFile;
use App\Models\ProcessingJob;
use App\Models\SystemSetting;
use App\Models\Tool;
use App\Models\UploadedFileRecord;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class FileSecurityService
{
    /**
     * Allowed MIME prefixes and explicit types per extension.
     */
    protected array $extensionMimeMap = [
        'pdf' => ['application/pdf', 'application/x-pdf', 'text/plain', 'application/octet-stream'],
        'jpg' => ['image/jpeg', 'image/pjpeg', 'application/octet-stream'],
        'jpeg' => ['image/jpeg', 'image/pjpeg', 'application/octet-stream'],
        'png' => ['image/png', 'application/octet-stream'],
        'webp' => ['image/webp', 'application/octet-stream'],
        'gif' => ['image/gif', 'application/octet-stream'],
        'txt' => ['text/plain', 'application/octet-stream', 'text/x-Algol68'],
        'md' => ['text/markdown', 'text/plain', 'application/octet-stream'],
        'csv' => ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel', 'application/octet-stream'],
        'json' => ['application/json', 'text/plain', 'application/octet-stream'],
        'html' => ['text/html', 'text/plain', 'application/octet-stream'],
        'htm' => ['text/html', 'text/plain', 'application/octet-stream'],
        'rtf' => ['application/rtf', 'text/rtf', 'text/plain', 'application/octet-stream'],
        'doc' => ['application/msword', 'text/plain', 'application/octet-stream', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'text/plain', 'application/octet-stream'],
        'xls' => ['application/vnd.ms-excel', 'text/csv', 'text/plain', 'application/octet-stream'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'text/csv', 'text/plain', 'application/octet-stream'],
        'ppt' => ['application/vnd.ms-powerpoint', 'text/plain', 'application/octet-stream'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip', 'text/plain', 'application/octet-stream'],
        'log' => ['text/plain', 'application/octet-stream'],
        'bin' => ['application/octet-stream', 'text/plain'],
    ];

    /**
     * Sanitize a filename to prevent directory traversal, null-byte injection, and executable extensions.
     */
    public function sanitizeFilename(string $filename): string
    {
        $filename = str_replace(["\0", '..', '/', '\\'], '', $filename);
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $cleanBase = preg_replace('/[^A-Za-z0-9_\-\s]/', '_', $base) ?: 'document';
        $cleanBase = Str::limit(trim($cleanBase), 80, '');

        // Block dangerous server executable extensions
        $blockedExtensions = ['php', 'phtml', 'phar', 'exe', 'sh', 'bat', 'cmd', 'dll', 'jsp', 'asp', 'aspx', 'cgi', 'pl'];
        if (in_array($ext, $blockedExtensions, true)) {
            throw new InvalidArgumentException("Security Policy Violation: Executable file extension (.{$ext}) is prohibited.");
        }

        return $ext ? "{$cleanBase}.{$ext}" : $cleanBase;
    }

    /**
     * Validate and store an uploaded file securely in private storage.
     */
    public function validateAndStoreUpload(
        UploadedFile $file,
        Tool $tool,
        ?User $user = null,
        ?ProcessingJob $job = null
    ): UploadedFileRecord {
        $originalName = $this->sanitizeFilename($file->getClientOriginalName());
        $ext = strtolower($file->getClientOriginalExtension() ?: pathinfo($originalName, PATHINFO_EXTENSION));

        // 1. Validate against tool's accepted formats
        $accepted = array_map('strtolower', $tool->accepted_formats ?? []);
        if (!empty($accepted) && !in_array($ext, $accepted, true)) {
            throw new InvalidArgumentException(
                "Unsupported file format (.{$ext}) for {$tool->name}. Allowed formats: " . implode(', ', $accepted)
            );
        }

        // 2. Validate file size against user tier & tool limits
        $maxSizeMb = $user ? $user->maxFileSizeMb($tool) : ($tool->free_max_file_size_mb ?: 25);
        $maxBytes = $maxSizeMb * 1024 * 1024;
        if ($file->getSize() > $maxBytes) {
            throw new InvalidArgumentException(
                "File size exceeds your plan limit of {$maxSizeMb} MB. Upgrade to Premium for up to 250 MB uploads."
            );
        }

        // 3. Validate MIME type
        $mime = strtolower($file->getMimeType() ?: $file->getClientMimeType() ?: 'application/octet-stream');
        if (isset($this->extensionMimeMap[$ext])) {
            $allowedMimes = $this->extensionMimeMap[$ext];
            if (!in_array($mime, $allowedMimes, true) && !str_starts_with($mime, 'text/') && !str_starts_with($mime, 'image/')) {
                throw new InvalidArgumentException("MIME type ({$mime}) does not match file extension (.{$ext}).");
            }
        }

        // 4. Scan file content for embedded PHP shells or EICAR malware signatures
        $rawSample = (string) @file_get_contents($file->getRealPath(), false, null, 0, 8192);
        if (str_contains($rawSample, '<?php') || str_contains($rawSample, 'X5O!P%@AP[4\PZX54(P^)7CC)7}$EICAR')) {
            throw new InvalidArgumentException('Security Alert: File failed malware / script injection inspection.');
        }

        $disk = (string) SystemSetting::getValue('active_disk', 'local');
        $uuid = (string) Str::uuid();
        $storedName = $uuid . '.' . ($ext ?: 'dat');
        $relativeDir = 'doctix/uploads/' . date('Y/m/d');
        $storedPath = $file->storeAs($relativeDir, $storedName, ['disk' => $disk]);

        $retentionHours = (int) SystemSetting::getValue('temp_file_retention_hours', config('doctix.temp_file_retention_hours', 24));

        return UploadedFileRecord::create([
            'file_uuid' => $uuid,
            'user_id' => $user?->id,
            'tool_id' => $tool->id,
            'processing_job_id' => $job?->id,
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'disk' => $disk,
            'file_path' => $storedPath,
            'mime_type' => $mime,
            'extension' => $ext ?: 'dat',
            'file_size' => (int) $file->getSize(),
            'checksum' => @hash_file('sha256', $file->getRealPath()) ?: null,
            'virus_scan_status' => 'clean',
            'expires_at' => now()->addHours($retentionHours),
            'is_deleted' => false,
        ]);
    }

    /**
     * Enforce strict file ownership: a user cannot access another user's uploaded or processed file.
     */
    public function canAccessProcessedFile(ProcessedFile $file, ?User $user, ?string $sessionJobUuid = null): bool
    {
        if ($file->is_deleted) {
            return false;
        }

        if ($file->expires_at && $file->expires_at->isPast()) {
            return false;
        }

        if ($user && $user->isAdmin()) {
            return true;
        }

        // If the file belongs to a registered user, ONLY that user can access it
        if ($file->user_id !== null) {
            return $user !== null && (int) $file->user_id === (int) $user->id;
        }

        // Guest file: verify matching job UUID in session
        if ($sessionJobUuid && $file->processingJob && $file->processingJob->job_uuid === $sessionJobUuid) {
            return true;
        }

        return false;
    }

    public function canAccessUploadedFile(UploadedFileRecord $file, ?User $user): bool
    {
        if ($file->is_deleted) {
            return false;
        }

        if ($user && $user->isAdmin()) {
            return true;
        }

        if ($file->user_id !== null) {
            return $user !== null && (int) $file->user_id === (int) $user->id;
        }

        return false;
    }

    /**
     * Automatically purge expired temporary uploads and processed output files.
     */
    public function cleanupExpiredFiles(): array
    {
        $uploadsDeleted = 0;
        $processedDeleted = 0;

        $expiredUploads = UploadedFileRecord::where('is_deleted', false)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($expiredUploads as $upload) {
            if (Storage::disk($upload->disk)->exists($upload->file_path)) {
                Storage::disk($upload->disk)->delete($upload->file_path);
            }
            $upload->update(['is_deleted' => true]);
            $uploadsDeleted++;
        }

        $expiredProcessed = ProcessedFile::where('is_deleted', false)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($expiredProcessed as $proc) {
            if (Storage::disk($proc->disk)->exists($proc->file_path)) {
                Storage::disk($proc->disk)->delete($proc->file_path);
            }
            $proc->update(['is_deleted' => true]);
            $processedDeleted++;
        }

        return [
            'uploads_deleted' => $uploadsDeleted,
            'processed_deleted' => $processedDeleted,
        ];
    }
}

