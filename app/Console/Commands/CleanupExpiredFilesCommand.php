<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\FileSecurityService;
use Illuminate\Console\Command;

class CleanupExpiredFilesCommand extends Command
{
    protected $signature = 'doctix:maintenance';
    protected $description = 'Purge expired temporary uploads/processed files and update expired subscriptions';

    public function handle(FileSecurityService $fileSecurity): int
    {
        $stats = $fileSecurity->cleanupExpiredFiles();

        $expiredSubs = Subscription::whereIn('status', ['active', 'canceled', 'grace_period'])
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('grace_ends_at')->orWhere('grace_ends_at', '<=', now());
            })
            ->update(['status' => 'expired']);

        $this->info("Purged {$stats['uploads_deleted']} uploads, {$stats['processed_deleted']} processed files, and marked {$expiredSubs} subscriptions expired.");
        return self::SUCCESS;
    }
}

