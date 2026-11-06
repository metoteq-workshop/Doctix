<?php

namespace App\Http\Controllers;

use App\Models\ProcessedFile;
use App\Models\Tool;
use App\Models\ToolCategory;
use App\Models\ToolUsageLog;
use App\Models\UploadedFileRecord;
use App\Services\FileSecurityService;
use App\Services\ToolProcessingManager;
use App\Services\UsageEnforcementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ToolController extends Controller
{
    public function __construct(
        protected UsageEnforcementService $usageService,
        protected ToolProcessingManager $processingManager,
        protected FileSecurityService $fileSecurity
    ) {}

    /**
     * Searchable Tools Directory with category filters, featured/popular/new badges, and live daily usage counters.
     */
    public function index(Request $request)
    {
        $categories = ToolCategory::where('is_active', true)->orderBy('sort_order')->get();

        $query = Tool::with('category')->where('is_active', true);

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('seo_keywords', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $catSlug = (string) $request->input('category');
            $query->whereHas('category', fn ($q) => $q->where('slug', $catSlug));
        }

        if ($request->filled('filter')) {
            match ($request->input('filter')) {
                'popular' => $query->where('is_popular', true),
                'featured' => $query->where('is_featured', true),
                'new' => $query->where('is_new', true),
                default => null,
            };
        }

        $tools = $query->orderBy('sort_order')->orderBy('name')->get();

        $user = $request->user();
        $today = $this->usageService->currentUsageDate();
        $usageCounts = [];

        if ($user) {
            $usageCounts = ToolUsageLog::query()
                ->select('tool_id', DB::raw('COUNT(*) as total'))
                ->where('user_id', $user->id)
                ->whereDate('usage_date', $today)
                ->where('status', 'completed')
                ->groupBy('tool_id')
                ->pluck('total', 'tool_id')
                ->all();
        }

        $isUnlimited = $user ? $user->hasActivePremiumSubscription() : false;

        return view('tools.index', compact(
            'categories',
            'tools',
            'usageCounts',
            'isUnlimited'
        ));
    }

    /**
     * Individual SEO-friendly Tool Workspace Page (/tools/{tool:slug}).
     */
    public function show(Request $request, Tool $tool)
    {
        if (!$tool->is_active) {
            abort(404);
        }

        $tool->load('category');
        $allowance = $this->usageService->checkAllowance($request->user(), $tool, $request->ip());

        $relatedTools = Tool::where('tool_category_id', $tool->tool_category_id)
            ->where('id', '!=', $tool->id)
            ->where('is_active', true)
            ->take(4)
            ->get();

        return view('tools.show', compact('tool', 'allowance', 'relatedTools'));
    }

    /**
     * Process an uploaded file or text payload with strict backend usage enforcement.
     */
    public function process(Request $request, Tool $tool)
    {
        $uploadedFiles = [];

        if ($request->hasFile('files')) {
            $rawFiles = $request->file('files');
            $uploadedFiles = is_array($rawFiles) ? $rawFiles : [$rawFiles];
        } elseif ($request->hasFile('file')) {
            $uploadedFiles = [$request->file('file')];
        }

        $options = $request->except(['_token', 'file', 'files', 'idempotency_key']);
        $idempotencyKey = $request->input('idempotency_key') ?: $request->header('X-Idempotency-Key');

        $result = $this->processingManager->process(
            tool: $tool,
            user: $request->user(),
            uploadedFiles: $uploadedFiles,
            options: $options,
            ipAddress: $request->ip(),
            idempotencyKey: $idempotencyKey ? (string) $idempotencyKey : null,
            sessionId: $request->hasSession() ? $request->session()->getId() : null
        );

        if ($result['success'] && isset($result['job']) && $request->hasSession()) {
            $request->session()->put('last_processed_job_uuid', $result['job']->job_uuid);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'upgrade_required' => $result['upgrade_required'] ?? false,
                'upgrade_url' => route('pricing'),
                'allowance' => $result['check'] ?? null,
                'job_uuid' => $result['job']->job_uuid ?? null,
                'download_url' => isset($result['processed_file'])
                    ? route('files.download', $result['processed_file']->download_token)
                    : null,
                'file_name' => $result['processed_file']->file_name ?? null,
                'file_size' => isset($result['processed_file']) ? $result['processed_file']->formattedSize() : null,
                'preview_text' => $result['preview_text'] ?? null,
            ], $result['status_code']);
        }

        if (!$result['success']) {
            return back()
                ->withInput()
                ->with('error', $result['message'])
                ->with('upgrade_required', $result['upgrade_required'] ?? false);
        }

        return back()
            ->with('success', $result['message'])
            ->with('processed_file_token', $result['processed_file']->download_token)
            ->with('processed_file_name', $result['processed_file']->file_name)
            ->with('processed_file_size', $result['processed_file']->formattedSize())
            ->with('preview_text', $result['preview_text'] ?? null);
    }

    /**
     * Download a processed output file with strict ownership enforcement.
     */
    public function download(Request $request, string $token)
    {
        $file = ProcessedFile::with('processingJob')->where('download_token', $token)->firstOrFail();
        $sessionJobUuid = $request->hasSession() ? $request->session()->get('last_processed_job_uuid') : null;

        if (!$this->fileSecurity->canAccessProcessedFile($file, $request->user(), $sessionJobUuid)) {
            abort(403, 'Unauthorized access: You do not have permission to download another user\'s file.');
        }

        if (!Storage::disk($file->disk)->exists($file->file_path)) {
            abort(404, 'The requested file has expired or been cleaned up.');
        }

        $file->increment('download_count');

        return Storage::disk($file->disk)->download($file->file_path, $file->file_name, [
            'Content-Type' => $file->mime_type,
        ]);
    }

    /**
     * Inspect or download an uploaded source file — strictly restricted to the file owner or admin.
     */
    public function viewUpload(Request $request, string $uuid)
    {
        $upload = UploadedFileRecord::where('file_uuid', $uuid)->firstOrFail();

        if (!$this->fileSecurity->canAccessUploadedFile($upload, $request->user())) {
            abort(403, 'Unauthorized access: You cannot access another user\'s uploaded document.');
        }

        if (!Storage::disk($upload->disk)->exists($upload->file_path)) {
            abort(404, 'Uploaded file not found.');
        }

        return Storage::disk($upload->disk)->download($upload->file_path, $upload->original_name, [
            'Content-Type' => $upload->mime_type,
        ]);
    }
}

