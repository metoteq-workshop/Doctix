<?php

return [
    'name' => env('APP_NAME', 'Doctix'),
    'tagline' => 'Enterprise-Grade PDF, Image, Document & AI Productivity Platform',
    'daily_free_limit' => (int) env('DOCTIX_DAILY_FREE_LIMIT', 4),
    'allow_guest_tools' => (bool) env('DOCTIX_ALLOW_GUEST_TOOLS', true),
    'guest_daily_limit' => (int) env('DOCTIX_GUEST_DAILY_LIMIT', 2),
    'free_max_file_size_mb' => (int) env('DOCTIX_FREE_MAX_FILE_SIZE_MB', 25),
    'premium_max_file_size_mb' => (int) env('DOCTIX_PREMIUM_MAX_FILE_SIZE_MB', 250),
    'temp_file_retention_hours' => (int) env('DOCTIX_TEMP_FILE_RETENTION_HOURS', 24),
    'currency' => env('DOCTIX_CURRENCY', 'USD'),
    'currency_symbol' => env('DOCTIX_CURRENCY_SYMBOL', '$'),
    'tax_percentage' => (float) env('DOCTIX_TAX_PERCENTAGE', 0.0),

    'ai' => [
        'provider' => env('DOCTIX_AI_PROVIDER', 'openai'), // openai, gemini, local
        'openai_api_key' => env('OPENAI_API_KEY', ''),
        'openai_model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'gemini_api_key' => env('GEMINI_API_KEY', ''),
        'gemini_model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
    ],

    'binaries' => [
        'ghostscript' => env('GHOSTSCRIPT_BINARY', 'gs'),
        'libreoffice' => env('LIBREOFFICE_BINARY', 'soffice'),
        'tesseract' => env('TESSERACT_BINARY', 'tesseract'),
        'clamav' => env('CLAMAV_BINARY', 'clamscan'),
    ],
];

