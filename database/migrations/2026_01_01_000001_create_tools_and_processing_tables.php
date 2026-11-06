<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tool_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->default('bi-folder');
            $table->string('badge_color')->default('primary');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();
        });

        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description', 500);
            $table->longText('description')->nullable();
            $table->string('icon')->default('bi-file-earmark-pdf');
            $table->string('accent_color')->default('#4F46E5');
            $table->string('engine_driver')->default('pdf'); // pdf, image, document, ai, utility
            $table->string('handler_action')->default('default'); // specific operation key
            $table->string('input_mode')->default('file'); // file, multi_file, text, form_or_file
            $table->json('accepted_formats')->nullable(); // ["pdf"], ["jpg","png"]
            $table->string('output_format')->default('pdf');
            $table->integer('free_max_file_size_mb')->default(25);
            $table->integer('premium_max_file_size_mb')->default(250);
            $table->integer('max_files')->default(10);
            $table->integer('daily_free_limit')->default(4);
            $table->boolean('is_premium_only')->default(false)->index();
            $table->boolean('allow_guest')->default(true);
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_popular')->default(false)->index();
            $table->boolean('is_new')->default(false)->index();
            $table->string('availability_status')->default('available'); // available, maintenance, coming_soon
            $table->boolean('queue_processing')->default(false);
            $table->unsignedBigInteger('total_usage_count')->default(0);
            $table->integer('sort_order')->default(0);
            $table->json('instructions')->nullable();
            $table->json('faqs')->nullable();
            $table->json('options_schema')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->timestamps();
        });

        Schema::create('tool_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained()->cascadeOnDelete();
            $table->string('setting_key');
            $table->text('setting_value')->nullable();
            $table->unique(['tool_id', 'setting_key']);
            $table->timestamps();
        });

        Schema::create('processing_jobs', function (Blueprint $table) {
            $table->id();
            $table->uuid('job_uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tool_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('status')->default('pending')->index(); // pending, processing, completed, failed
            $table->integer('progress')->default(0);
            $table->json('options')->nullable();
            $table->json('result_metadata')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('processing_time_ms')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('uploaded_files', function (Blueprint $table) {
            $table->id();
            $table->uuid('file_uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tool_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('processing_job_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('disk')->default('local');
            $table->string('file_path');
            $table->string('mime_type', 120);
            $table->string('extension', 25);
            $table->unsignedBigInteger('file_size'); // bytes
            $table->string('checksum', 64)->nullable();
            $table->string('virus_scan_status')->default('clean');
            $table->timestamp('expires_at')->nullable()->index();
            $table->boolean('is_deleted')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('processed_files', function (Blueprint $table) {
            $table->id();
            $table->uuid('download_token')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tool_id')->constrained()->cascadeOnDelete();
            $table->foreignId('processing_job_id')->constrained()->cascadeOnDelete();
            $table->string('file_name');
            $table->string('disk')->default('local');
            $table->string('file_path');
            $table->string('mime_type', 120);
            $table->string('extension', 25);
            $table->unsignedBigInteger('file_size');
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamp('expires_at')->nullable()->index();
            $table->boolean('is_deleted')->default(false)->index();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('tool_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tool_id')->constrained()->cascadeOnDelete();
            $table->foreignId('processing_job_id')->nullable()->constrained()->nullOnDelete();
            $table->date('usage_date')->index();
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('session_id', 120)->nullable();
            $table->string('idempotency_key', 120)->unique();
            $table->string('plan_tier')->default('free'); // free, premium, guest, admin_override
            $table->string('status')->default('completed')->index(); // reserved, completed, failed, reset_by_admin
            $table->unsignedBigInteger('input_bytes')->default(0);
            $table->unsignedBigInteger('output_bytes')->default(0);
            $table->integer('processing_time_ms')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'tool_id', 'usage_date', 'status'], 'idx_user_tool_date_status');
            $table->index(['ip_address', 'tool_id', 'usage_date', 'status'], 'idx_ip_tool_date_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tool_usage_logs');
        Schema::dropIfExists('processed_files');
        Schema::dropIfExists('uploaded_files');
        Schema::dropIfExists('processing_jobs');
        Schema::dropIfExists('tool_settings');
        Schema::dropIfExists('tools');
        Schema::dropIfExists('tool_categories');
    }
};

