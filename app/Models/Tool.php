<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tool extends Model
{
    protected $fillable = [
        'tool_category_id',
        'name',
        'slug',
        'short_description',
        'description',
        'icon',
        'accent_color',
        'engine_driver',
        'handler_action',
        'input_mode',
        'accepted_formats',
        'output_format',
        'free_max_file_size_mb',
        'premium_max_file_size_mb',
        'max_files',
        'daily_free_limit',
        'is_premium_only',
        'allow_guest',
        'is_active',
        'is_featured',
        'is_popular',
        'is_new',
        'availability_status',
        'queue_processing',
        'total_usage_count',
        'sort_order',
        'instructions',
        'faqs',
        'options_schema',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

    protected function casts(): array
    {
        return [
            'accepted_formats' => 'array',
            'instructions' => 'array',
            'faqs' => 'array',
            'options_schema' => 'array',
            'is_premium_only' => 'boolean',
            'allow_guest' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_popular' => 'boolean',
            'is_new' => 'boolean',
            'queue_processing' => 'boolean',
            'free_max_file_size_mb' => 'integer',
            'premium_max_file_size_mb' => 'integer',
            'max_files' => 'integer',
            'daily_free_limit' => 'integer',
            'total_usage_count' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ToolCategory::class, 'tool_category_id');
    }

    public function settings(): HasMany
    {
        return $this->hasMany(ToolSetting::class);
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(ToolUsageLog::class);
    }

    public function processingJobs(): HasMany
    {
        return $this->hasMany(ProcessingJob::class);
    }

    public function effectiveDailyFreeLimit(?User $user = null): int
    {
        if ($user && $user->custom_daily_limit !== null) {
            return (int) $user->custom_daily_limit;
        }

        if ($this->daily_free_limit > 0) {
            return (int) $this->daily_free_limit;
        }

        return (int) SystemSetting::getValue('daily_free_limit', config('doctix.daily_free_limit', 4));
    }

    public function acceptedExtensionsString(): string
    {
        $formats = $this->accepted_formats ?? [];
        if (empty($formats)) {
            return '*';
        }
        return implode(', ', array_map(fn ($ext) => '.' . ltrim(strtolower($ext), '.'), $formats));
    }
}

