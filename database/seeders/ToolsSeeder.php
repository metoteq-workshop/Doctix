<?php

namespace Database\Seeders;

use App\Models\Tool;
use App\Models\ToolCategory;
use Illuminate\Database\Seeder;

class ToolsSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'pdf' => ToolCategory::updateOrCreate(
                ['slug' => 'pdf-tools'],
                [
                    'name' => 'PDF Tools',
                    'icon' => 'bi-file-earmark-pdf-fill',
                    'badge_color' => 'danger',
                    'description' => 'Merge, split, compress, edit, protect, unlock, sign, and convert PDF documents with enterprise precision.',
                    'sort_order' => 1,
                    'is_active' => true,
                    'seo_title' => 'Online PDF Tools — Merge, Split, Compress & Convert PDFs | Doctix',
                    'seo_description' => 'Complete suite of fast, secure online PDF tools. Merge, split, compress, rotate, sign, and convert PDF files in seconds.',
                ]
            ),
            'image' => ToolCategory::updateOrCreate(
                ['slug' => 'image-tools'],
                [
                    'name' => 'Image Tools',
                    'icon' => 'bi-images',
                    'badge_color' => 'primary',
                    'description' => 'Compress, resize, crop, convert, watermark, enhance, and transform images across JPG, PNG, WebP, and PDF.',
                    'sort_order' => 2,
                    'is_active' => true,
                    'seo_title' => 'Online Image Tools — Compress, Resize, Convert & Enhance | Doctix',
                    'seo_description' => 'Optimize and transform images online. Convert between JPG, PNG, WebP, and PDF, resize, crop, and watermark images.',
                ]
            ),
            'document' => ToolCategory::updateOrCreate(
                ['slug' => 'document-tools'],
                [
                    'name' => 'Document Tools',
                    'icon' => 'bi-file-earmark-richtext-fill',
                    'badge_color' => 'info',
                    'description' => 'Convert, merge, compress, and translate Word, Excel, PowerPoint, HTML, TXT, and RTF business documents.',
                    'sort_order' => 3,
                    'is_active' => true,
                    'seo_title' => 'Document Conversion & Management Tools | Doctix',
                    'seo_description' => 'Convert Word, Excel, PowerPoint, HTML, RTF, and TXT documents to PDF and manage multi-format files seamlessly.',
                ]
            ),
            'ai' => ToolCategory::updateOrCreate(
                ['slug' => 'ai-tools'],
                [
                    'name' => 'AI-Powered Tools',
                    'icon' => 'bi-stars',
                    'badge_color' => 'purple',
                    'description' => 'Summarize PDFs, ask questions about documents, rewrite text, check grammar, translate, and build resumes with AI.',
                    'sort_order' => 4,
                    'is_active' => true,
                    'seo_title' => 'AI Document & Productivity Tools — Summarizer, Q&A, Rewriter | Doctix',
                    'seo_description' => 'Supercharge document workflows with AI PDF summarizers, document Q&A, grammar checking, paraphrasing, and resume building.',
                ]
            ),
            'utility' => ToolCategory::updateOrCreate(
                ['slug' => 'productivity-tools'],
                [
                    'name' => 'Productivity Tools',
                    'icon' => 'bi-lightning-charge-fill',
                    'badge_color' => 'success',
                    'description' => 'Generate QR codes and barcodes, count words, format JSON, encode Base64, convert CSV to Excel, and render Markdown to PDF.',
                    'sort_order' => 5,
                    'is_active' => true,
                    'seo_title' => 'Online Developer & Productivity Utilities | Doctix',
                    'seo_description' => 'Instant QR code generator, barcode creator, word counter, JSON formatter, Base64 encoder, and Markdown to PDF converter.',
                ]
            ),
        ];

        $tools = [
            // ==========================================
            // A. PDF TOOLS (28 Tools)
            // ==========================================
            [
                'cat' => 'pdf', 'name' => 'Merge PDF', 'slug' => 'merge-pdf', 'icon' => 'bi-union', 'color' => '#EF4444',
                'engine' => 'pdf', 'action' => 'merge_pdf', 'input' => 'multi_file', 'formats' => ['pdf'], 'out' => 'pdf',
                'featured' => true, 'popular' => true,
                'short' => 'Combine multiple PDF documents into a single organized PDF file in seconds.',
                'options' => [['name' => 'title', 'label' => 'Merged Document Title (Optional)', 'type' => 'text', 'placeholder' => 'Combined Document']],
            ],
            [
                'cat' => 'pdf', 'name' => 'Split PDF', 'slug' => 'split-pdf', 'icon' => 'bi-scissors', 'color' => '#F97316',
                'engine' => 'pdf', 'action' => 'split_pdf', 'input' => 'file', 'formats' => ['pdf'], 'out' => 'pdf',
                'featured' => true, 'popular' => true,
                'short' => 'Extract specific page ranges or split a PDF document into separate pages.',
                'options' => [
                    ['name' => 'page_range', 'label' => 'Page Range (e.g. 1-3 or 1,3,5)', 'type' => 'text', 'default' => '1', 'placeholder' => '1-2'],
                ],
            ],
            [
                'cat' => 'pdf', 'name' => 'Compress PDF', 'slug' => 'compress-pdf', 'icon' => 'bi-file-earmark-zip-fill', 'color' => '#10B981',
                'engine' => 'pdf', 'action' => 'compress_pdf', 'input' => 'file', 'formats' => ['pdf'], 'out' => 'pdf',
                'featured' => true, 'popular' => true,
                'short' => 'Reduce PDF file size while preserving crisp text and visual document quality.',
                'options' => [
                    ['name' => 'compression_level', 'label' => 'Compression Level', 'type' => 'select', 'options' => ['recommended' => 'Recommended Compression (Balanced)', 'extreme' => 'Maximum Compression (Smallest Size)', 'light' => 'Light Compression (High Quality)'], 'default' => 'recommended'],
                ],
            ],
            [
                'cat' => 'pdf', 'name' => 'Edit PDF', 'slug' => 'edit-pdf', 'icon' => 'bi-pencil-square', 'color' => '#4F46E5',
                'engine' => 'pdf', 'action' => 'edit_pdf', 'input' => 'file', 'formats' => ['pdf'], 'out' => 'pdf',
                'featured' => true, 'popular' => true,
                'short' => 'Add custom text annotations, header/footer notes, and metadata updates to your PDF.',
                'options' => [
                    ['name' => 'annotation_text', 'label' => 'Annotation / Stamp Text', 'type' => 'text', 'default' => 'Edited with Doctix', 'placeholder' => 'Enter text to place on document'],
                    ['name' => 'position', 'label' => 'Placement Position', 'type' => 'select', 'options' => ['top' => 'Top Header', 'center' => 'Center Overlay', 'bottom' => 'Bottom Footer'], 'default' => 'bottom'],
                ],
            ],
            [
                'cat' => 'pdf', 'name' => 'Rotate PDF', 'slug' => 'rotate-pdf', 'icon' => 'bi-arrow-clockwise', 'color' => '#6366F1',
                'engine' => 'pdf', 'action' => 'rotate_pdf', 'input' => 'file', 'formats' => ['pdf'], 'out' => 'pdf',
                'featured' => false, 'popular' => true,
                'short' => 'Rotate all pages in your PDF document by 90, 180, or 270 degrees.',
                'options' => [
                    ['name' => 'angle', 'label' => 'Rotation Angle', 'type' => 'select', 'options' => ['90' => '90° Clockwise', '180' => '180° Upside Down', '270' => '270° Counter-Clockwise'], 'default' => '90'],
                ],
            ],
            [
                'cat' => 'pdf', 'name' => 'Delete PDF Pages', 'slug' => 'delete-pdf-pages', 'icon' => 'bi-file-earmark-minus-fill', 'color' => '#DC2626',
                'engine' => 'pdf', 'action' => 'delete_pdf_pages', 'input' => 'file', 'formats' => ['pdf'], 'out' => 'pdf',
                'short' => 'Remove unwanted pages from any PDF file and save the cleaned document.',
                'options' => [
                    ['name' => 'pages_to_delete', 'label' => 'Pages to Remove (comma-separated, e.g. 2,4)', 'type' => 'text', 'default' => '2', 'placeholder' => '2'],
                ],
            ],
            [
                'cat' => 'pdf', 'name' => 'Extract PDF Pages', 'slug' => 'extract-pdf-pages', 'icon' => 'bi-files', 'color' => '#0EA5E9',
                'engine' => 'pdf', 'action' => 'extract_pdf_pages', 'input' => 'file', 'formats' => ['pdf'], 'out' => 'pdf',
                'short' => 'Create a new PDF containing only the exact pages you select.',
                'options' => [
                    ['name' => 'pages_to_extract', 'label' => 'Pages to Extract (e.g. 1,3 or 1-2)', 'type' => 'text', 'default' => '1', 'placeholder' => '1'],
                ],
            ],
            [
                'cat' => 'pdf', 'name' => 'Organize PDF Pages', 'slug' => 'organize-pdf-pages', 'icon' => 'bi-grid-3x3-gap-fill', 'color' => '#8B5CF6',
                'engine' => 'pdf', 'action' => 'organize_pdf_pages', 'input' => 'file', 'formats' => ['pdf'], 'out' => 'pdf',
                'short' => 'Reorder or reverse the page sequence of your PDF document effortlessly.',
                'options' => [
                    ['name' => 'page_order', 'label' => 'Page Order (e.g. reverse or 2,1,3)', 'type' => 'text', 'default' => 'reverse', 'placeholder' => 'reverse or 2,1,3'],
                ],
            ],
            [
                'cat' => 'pdf', 'name' => 'Protect PDF', 'slug' => 'protect-pdf', 'icon' => 'bi-shield-lock-fill', 'color' => '#059669',
                'engine' => 'pdf', 'action' => 'protect_pdf', 'input' => 'file', 'formats' => ['pdf'], 'out' => 'pdf',
                'featured' => true,
                'short' => 'Apply security protection metadata, confidential classification headers, and access controls to your PDF.',
                'options' => [
                    ['name' => 'password', 'label' => 'Document Access Password', 'type' => 'password', 'placeholder' => 'Enter strong password'],
                ],
            ],
            [
                'cat' => 'pdf', 'name' => 'Unlock PDF', 'slug' => 'unlock-pdf', 'icon' => 'bi-unlock-fill', 'color' => '#D97706',
                'engine' => 'pdf', 'action' => 'unlock_pdf', 'input' => 'file', 'formats' => ['pdf'], 'out' => 'pdf',
                'short' => 'Rebuild and strip owner restrictions from authorized PDF files for editing and printing.',
                'options' => [
                    ['name' => 'password', 'label' => 'Current Password (if known)', 'type' => 'password', 'placeholder' => 'Optional password'],
                ],
            ],
            [
                'cat' => 'pdf', 'name' => 'Add Watermark', 'slug' => 'add-watermark', 'icon' => 'bi-droplet-half', 'color' => '#3B82F6',
                'engine' => 'pdf', 'action' => 'watermark_pdf', 'input' => 'file', 'formats' => ['pdf'], 'out' => 'pdf',
                'popular' => true,
                'short' => 'Stamp custom text watermarks across every page of your PDF document.',
                'options' => [
                    ['name' => 'watermark_text', 'label' => 'Watermark Text', 'type' => 'text', 'default' => 'CONFIDENTIAL', 'placeholder' => 'CONFIDENTIAL / DRAFT'],
                ],
            ],
            [
                'cat' => 'pdf', 'name' => 'Add Page Numbers', 'slug' => 'add-page-numbers', 'icon' => 'bi-123', 'color' => '#7C3AED',
                'engine' => 'pdf', 'action' => 'page_numbers_pdf', 'input' => 'file', 'formats' => ['pdf'], 'out' => 'pdf',
                'short' => 'Insert formatted page numbering into the header or footer of every PDF page.',
                'options' => [
                    ['name' => 'format', 'label' => 'Numbering Style', 'type' => 'select', 'options' => ['page_of_total' => 'Page X of Y', 'simple' => 'X'], 'default' => 'page_of_total'],
                ],
            ],
            [
                'cat' => 'pdf', 'name' => 'Sign PDF', 'slug' => 'sign-pdf', 'icon' => 'bi-pen-fill', 'color' => '#0F172A',
                'engine' => 'pdf', 'action' => 'sign_pdf', 'input' => 'file', 'formats' => ['pdf'], 'out' => 'pdf',
                'featured' => true, 'popular' => true,
                'short' => 'Apply legally styled electronic signature blocks with timestamp and verification hash to PDFs.',
                'options' => [
                    ['name' => 'signer_name', 'label' => 'Full Legal Name of Signer', 'type' => 'text', 'default' => 'Authorized Signatory', 'placeholder' => 'Jane Doe'],
                    ['name' => 'signer_title', 'label' => 'Title / Role', 'type' => 'text', 'default' => 'Executive Director', 'placeholder' => 'CEO'],
                ],
            ],
            [
                'cat' => 'pdf', 'name' => 'Fill PDF Forms', 'slug' => 'fill-pdf-forms', 'icon' => 'bi-ui-checks', 'color' => '#2563EB',
                'engine' => 'pdf', 'action' => 'fill_pdf_forms', 'input' => 'file', 'formats' => ['pdf'], 'out' => 'pdf',
                'short' => 'Populate PDF form fields and append structured key-value data blocks to PDF forms.',
                'options' => [
                    ['name' => 'form_values', 'label' => 'Form Field Values (Key: Value per line)', 'type' => 'textarea', 'default' => "Applicant Name: Alex Morgan\nDate: " . date('Y-m-d') . "\nStatus: Approved"],
                ],
            ],
            [
                'cat' => 'pdf', 'name' => 'Convert PDF to Word', 'slug' => 'pdf-to-word', 'icon' => 'bi-file-earmark-word-fill', 'color' => '#1D4ED8',
                'engine' => 'pdf', 'action' => 'pdf_to_word', 'input' => 'file', 'formats' => ['pdf'], 'out' => 'doc',
                'featured' => true, 'popular' => true,
                'short' => 'Convert PDF documents into editable Microsoft Word (.doc) files while preserving text content.',
            ],
            [
                'cat' => 'pdf', 'name' => 'Convert Word to PDF', 'slug' => 'word-to-pdf', 'icon' => 'bi-file-earmark-pdf-fill', 'color' => '#E11D48',
                'engine' => 'document', 'action' => 'word_to_pdf', 'input' => 'file', 'formats' => ['doc', 'docx', 'txt', 'rtf'], 'out' => 'pdf',
                'featured' => true, 'popular' => true,
                'short' => 'Transform Word documents (.doc, .docx, .rtf) into universally compatible PDF files.',
            ],
            [
                'cat' => 'pdf', 'name' => 'Convert PDF to Excel', 'slug' => 'pdf-to-excel', 'icon' => 'bi-file-earmark-excel-fill', 'color' => '#15803D',
                'engine' => 'pdf', 'action' => 'pdf_to_excel', 'input' => 'file', 'formats' => ['pdf'], 'out' => 'csv',
                'popular' => true,
                'short' => 'Extract tabular data and text rows from PDF documents into Excel-ready spreadsheet files.',
            ],
            [
                'cat' => 'pdf', 'name' => 'Convert Excel to PDF', 'slug' => 'excel-to-pdf', 'icon' => 'bi-table', 'color' => '#16A34A',
                'engine' => 'document', 'action' => 'excel_to_pdf', 'input' => 'file', 'formats' => ['csv', 'xls', 'xlsx', 'txt'], 'out' => 'pdf',
                'short' => 'Render spreadsheets and tabular CSV/Excel files into clean, printable PDF reports.',
            ],
            [
                'cat' => 'pdf', 'name' => 'Convert PDF to PowerPoint', 'slug' => 'pdf-to-powerpoint', 'icon' => 'bi-file-earmark-slides-fill', 'color' => '#EA580C',
                'engine' => 'pdf', 'action' => 'pdf_to_ppt', 'input' => 'file', 'formats' => ['pdf'], 'out' => 'html',
                'short' => 'Transform PDF pages into structured presentation slide decks.',
            ],
            [
                'cat' => 'pdf', 'name' => 'Convert PowerPoint to PDF', 'slug' => 'powerpoint-to-pdf', 'icon' => 'bi-easel-fill', 'color' => '#C2410C',
                'engine' => 'document', 'action' => 'ppt_to_pdf', 'input' => 'file', 'formats' => ['ppt', 'pptx', 'txt', 'html'], 'out' => 'pdf',
                'short' => 'Convert presentation outlines and slide files into portable PDF handouts.',
            ],
            [
                'cat' => 'pdf', 'name' => 'Convert PDF to JPG', 'slug' => 'pdf-to-jpg', 'icon' => 'bi-filetype-jpg', 'color' => '#D97706',
                'engine' => 'image', 'action' => 'pdf_to_jpg', 'input' => 'file', 'formats' => ['pdf'], 'out' => 'jpg',
                'popular' => true,
                'short' => 'Render PDF document pages into high-resolution JPG image snapshots.',
            ],
            [
                'cat' => 'pdf', 'name' => 'Convert JPG to PDF', 'slug' => 'jpg-to-pdf', 'icon' => 'bi-file-earmark-image-fill', 'color' => '#2563EB',
                'engine' => 'image', 'action' => 'jpg_to_pdf', 'input' => 'multi_file', 'formats' => ['jpg', 'jpeg'], 'out' => 'pdf',
                'popular' => true,
                'short' => 'Combine one or more JPG photos into a clean, print-ready PDF document.',
            ],
            [
                'cat' => 'pdf', 'name' => 'Convert PDF to PNG', 'slug' => 'pdf-to-png', 'icon' => 'bi-filetype-png', 'color' => '#0284C7',
                'engine' => 'image', 'action' => 'pdf_to_png', 'input' => 'file', 'formats' => ['pdf'], 'out' => 'png',
                'short' => 'Convert PDF pages into lossless PNG raster graphics.',
            ],
            [
                'cat' => 'pdf', 'name' => 'Convert PNG to PDF', 'slug' => 'png-to-pdf', 'icon' => 'bi-file-image', 'color' => '#4F46E5',
                'engine' => 'image', 'action' => 'png_to_pdf', 'input' => 'multi_file', 'formats' => ['png'], 'out' => 'pdf',
                'short' => 'Convert PNG images into a standardized PDF document without quality loss.',
            ],
            [
                'cat' => 'pdf', 'name' => 'Convert PDF to Text', 'slug' => 'pdf-to-text', 'icon' => 'bi-file-earmark-text-fill', 'color' => '#475569',
                'engine' => 'pdf', 'action' => 'pdf_to_text', 'input' => 'file', 'formats' => ['pdf'], 'out' => 'txt',
                'short' => 'Extract raw plain text from PDF files for indexing, quoting, or editing.',
            ],
            [
                'cat' => 'pdf', 'name' => 'Convert PDF to HTML', 'slug' => 'pdf-to-html', 'icon' => 'bi-filetype-html', 'color' => '#E11D48',
                'engine' => 'pdf', 'action' => 'pdf_to_html', 'input' => 'file', 'formats' => ['pdf'], 'out' => 'html',
                'short' => 'Convert PDF documents into responsive, web-ready HTML5 pages.',
            ],
            [
                'cat' => 'pdf', 'name' => 'Repair PDF', 'slug' => 'repair-pdf', 'icon' => 'bi-wrench-adjustable-circle-fill', 'color' => '#0D9488',
                'engine' => 'pdf', 'action' => 'repair_pdf', 'input' => 'file', 'formats' => ['pdf'], 'out' => 'pdf',
                'short' => 'Rebuild cross-reference tables and recover content from damaged or malformed PDF streams.',
            ],
            [
                'cat' => 'pdf', 'name' => 'OCR PDF', 'slug' => 'ocr-pdf', 'icon' => 'bi-eye-fill', 'color' => '#7C3AED',
                'engine' => 'pdf', 'action' => 'ocr_pdf', 'input' => 'file', 'formats' => ['pdf'], 'out' => 'pdf',
                'new' => true,
                'short' => 'Perform optical character recognition and text layer extraction on PDF files.',
            ],

            // ==========================================
            // B. IMAGE TOOLS (14 Tools)
            // ==========================================
            [
                'cat' => 'image', 'name' => 'Compress Image', 'slug' => 'compress-image', 'icon' => 'bi-arrows-angle-contract', 'color' => '#0EA5E9',
                'engine' => 'image', 'action' => 'compress_image', 'input' => 'file', 'formats' => ['jpg', 'jpeg', 'png', 'webp'], 'out' => 'jpg',
                'featured' => true, 'popular' => true,
                'short' => 'Shrink JPG, PNG, and WebP file sizes by up to 80% while retaining visual clarity.',
                'options' => [
                    ['name' => 'quality', 'label' => 'Target Quality (10 - 95)', 'type' => 'number', 'default' => '75'],
                ],
            ],
            [
                'cat' => 'image', 'name' => 'Resize Image', 'slug' => 'resize-image', 'icon' => 'bi-aspect-ratio-fill', 'color' => '#2563EB',
                'engine' => 'image', 'action' => 'resize_image', 'input' => 'file', 'formats' => ['jpg', 'jpeg', 'png', 'webp'], 'out' => 'png',
                'popular' => true,
                'short' => 'Resize images to exact pixel dimensions or percentage scales smoothly.',
                'options' => [
                    ['name' => 'width', 'label' => 'Target Width (px)', 'type' => 'number', 'default' => '800'],
                    ['name' => 'height', 'label' => 'Target Height (px)', 'type' => 'number', 'default' => '600'],
                ],
            ],
            [
                'cat' => 'image', 'name' => 'Crop Image', 'slug' => 'crop-image', 'icon' => 'bi-crop', 'color' => '#4F46E5',
                'engine' => 'image', 'action' => 'crop_image', 'input' => 'file', 'formats' => ['jpg', 'jpeg', 'png', 'webp'], 'out' => 'png',
                'short' => 'Crop images to focus on the center region or custom bounding box coordinates.',
                'options' => [
                    ['name' => 'crop_ratio', 'label' => 'Aspect Ratio Preset', 'type' => 'select', 'options' => ['square' => '1:1 Square', 'widescreen' => '16:9 Widescreen', 'portrait' => '4:5 Portrait'], 'default' => 'square'],
                ],
            ],
            [
                'cat' => 'image', 'name' => 'Convert Image Formats', 'slug' => 'convert-image-formats', 'icon' => 'bi-arrow-left-right', 'color' => '#8B5CF6',
                'engine' => 'image', 'action' => 'convert_image', 'input' => 'file', 'formats' => ['jpg', 'jpeg', 'png', 'webp', 'gif'], 'out' => 'png',
                'short' => 'Universal image converter supporting JPG, PNG, WebP, and GIF formats.',
                'options' => [
                    ['name' => 'target_format', 'label' => 'Target Output Format', 'type' => 'select', 'options' => ['png' => 'PNG', 'jpg' => 'JPG', 'webp' => 'WebP'], 'default' => 'png'],
                ],
            ],
            [
                'cat' => 'image', 'name' => 'JPG to PNG', 'slug' => 'jpg-to-png', 'icon' => 'bi-filetype-png', 'color' => '#0284C7',
                'engine' => 'image', 'action' => 'jpg_to_png', 'input' => 'file', 'formats' => ['jpg', 'jpeg'], 'out' => 'png',
                'popular' => true,
                'short' => 'Convert JPG photographs into lossless PNG format instantaneously.',
            ],
            [
                'cat' => 'image', 'name' => 'PNG to JPG', 'slug' => 'png-to-jpg', 'icon' => 'bi-filetype-jpg', 'color' => '#EA580C',
                'engine' => 'image', 'action' => 'png_to_jpg', 'input' => 'file', 'formats' => ['png'], 'out' => 'jpg',
                'popular' => true,
                'short' => 'Convert PNG graphics with white background flattening into compact JPG images.',
            ],
            [
                'cat' => 'image', 'name' => 'WebP Converter', 'slug' => 'webp-converter', 'icon' => 'bi-Lightning-fill', 'color' => '#10B981',
                'engine' => 'image', 'action' => 'webp_converter', 'input' => 'file', 'formats' => ['jpg', 'jpeg', 'png', 'webp'], 'out' => 'webp',
                'new' => true,
                'short' => 'Convert JPG and PNG images to next-gen WebP format for faster web performance.',
            ],
            [
                'cat' => 'image', 'name' => 'Image Background Remover', 'slug' => 'image-background-remover', 'icon' => 'bi-person-bounding-box', 'color' => '#EC4899',
                'engine' => 'image', 'action' => 'remove_background', 'input' => 'file', 'formats' => ['jpg', 'jpeg', 'png'], 'out' => 'png',
                'featured' => true, 'new' => true,
                'short' => 'Automatically detect and make uniform background colors transparent in PNG output.',
            ],
            [
                'cat' => 'image', 'name' => 'Image Compressor', 'slug' => 'image-compressor', 'icon' => 'bi-file-zip-fill', 'color' => '#059669',
                'engine' => 'image', 'action' => 'compress_image', 'input' => 'file', 'formats' => ['jpg', 'jpeg', 'png', 'webp'], 'out' => 'jpg',
                'popular' => true,
                'short' => 'High-speed smart image optimizer for web, email, and social media assets.',
            ],
            [
                'cat' => 'image', 'name' => 'Image Rotator', 'slug' => 'image-rotator', 'icon' => 'bi-arrow-repeat', 'color' => '#6366F1',
                'engine' => 'image', 'action' => 'rotate_image', 'input' => 'file', 'formats' => ['jpg', 'jpeg', 'png', 'webp'], 'out' => 'png',
                'short' => 'Rotate or flip images clockwise or counter-clockwise with zero quality degradation.',
                'options' => [
                    ['name' => 'angle', 'label' => 'Rotation Angle', 'type' => 'select', 'options' => ['90' => '90° Clockwise', '180' => '180° Flip', '270' => '270° Counter-Clockwise'], 'default' => '90'],
                ],
            ],
            [
                'cat' => 'image', 'name' => 'Image Watermark', 'slug' => 'image-watermark', 'icon' => 'bi-award-fill', 'color' => '#3B82F6',
                'engine' => 'image', 'action' => 'watermark_image', 'input' => 'file', 'formats' => ['jpg', 'jpeg', 'png'], 'out' => 'png',
                'short' => 'Overlay copyright text or brand watermarks onto your photographs.',
                'options' => [
                    ['name' => 'watermark_text', 'label' => 'Watermark Text', 'type' => 'text', 'default' => '© Doctix Protected'],
                ],
            ],
            [
                'cat' => 'image', 'name' => 'Image to PDF', 'slug' => 'image-to-pdf', 'icon' => 'bi-file-earmark-pdf', 'color' => '#EF4444',
                'engine' => 'image', 'action' => 'image_to_pdf', 'input' => 'multi_file', 'formats' => ['jpg', 'jpeg', 'png'], 'out' => 'pdf',
                'short' => 'Compile any combination of JPG and PNG images into a clean multi-page PDF.',
            ],
            [
                'cat' => 'image', 'name' => 'PDF to Image', 'slug' => 'pdf-to-image', 'icon' => 'bi-card-image', 'color' => '#F59E0B',
                'engine' => 'image', 'action' => 'pdf_to_png', 'input' => 'file', 'formats' => ['pdf'], 'out' => 'png',
                'short' => 'Render PDF document pages into high-clarity PNG image files.',
            ],
            [
                'cat' => 'image', 'name' => 'Image Enhancer', 'slug' => 'image-enhancer', 'icon' => 'bi-magic', 'color' => '#7C3AED',
                'engine' => 'image', 'action' => 'enhance_image', 'input' => 'file', 'formats' => ['jpg', 'jpeg', 'png'], 'out' => 'png',
                'new' => true,
                'short' => 'Enhance image contrast, brightness, and sharpness automatically.',
            ],

            // ==========================================
            // C. DOCUMENT TOOLS (6 Unique + Cross-Format)
            // ==========================================
            [
                'cat' => 'document', 'name' => 'TXT to PDF', 'slug' => 'txt-to-pdf', 'icon' => 'bi-filetype-txt', 'color' => '#475569',
                'engine' => 'document', 'action' => 'txt_to_pdf', 'input' => 'file', 'formats' => ['txt', 'log', 'md'], 'out' => 'pdf',
                'short' => 'Convert plain text files into cleanly typeset, paginated PDF documents.',
            ],
            [
                'cat' => 'document', 'name' => 'HTML to PDF', 'slug' => 'html-to-pdf', 'icon' => 'bi-filetype-html', 'color' => '#E11D48',
                'engine' => 'document', 'action' => 'html_to_pdf', 'input' => 'file', 'formats' => ['html', 'htm', 'txt'], 'out' => 'pdf',
                'popular' => true,
                'short' => 'Convert HTML files and web markup into structured, printable PDF documents.',
            ],
            [
                'cat' => 'document', 'name' => 'RTF to PDF', 'slug' => 'rtf-to-pdf', 'icon' => 'bi-file-earmark-richtext', 'color' => '#2563EB',
                'engine' => 'document', 'action' => 'rtf_to_pdf', 'input' => 'file', 'formats' => ['rtf', 'txt'], 'out' => 'pdf',
                'short' => 'Convert Rich Text Format (.rtf) documents into standard PDF files.',
            ],
            [
                'cat' => 'document', 'name' => 'Document Merger', 'slug' => 'document-merger', 'icon' => 'bi-files-alt', 'color' => '#4F46E5',
                'engine' => 'document', 'action' => 'merge_documents', 'input' => 'multi_file', 'formats' => ['txt', 'md', 'html', 'csv', 'pdf'], 'out' => 'pdf',
                'short' => 'Combine multiple text, markdown, HTML, or PDF documents into one unified PDF.',
            ],
            [
                'cat' => 'document', 'name' => 'Document Compressor', 'slug' => 'document-compressor', 'icon' => 'bi-file-earmark-zip', 'color' => '#059669',
                'engine' => 'document', 'action' => 'compress_document', 'input' => 'file', 'formats' => ['pdf', 'txt', 'html', 'csv', 'json'], 'out' => 'pdf',
                'short' => 'Optimize and minify document streams to reduce storage and email attachment footprint.',
            ],
            [
                'cat' => 'document', 'name' => 'Document Translation', 'slug' => 'document-translation', 'icon' => 'bi-translate', 'color' => '#0284C7',
                'engine' => 'ai', 'action' => 'translate_document', 'input' => 'form_or_file', 'formats' => ['txt', 'pdf', 'md', 'html'], 'out' => 'pdf',
                'short' => 'Translate documents across major world languages while generating a clean PDF output.',
                'options' => [
                    ['name' => 'target_language', 'label' => 'Target Language', 'type' => 'select', 'options' => ['Spanish' => 'Spanish', 'French' => 'French', 'German' => 'German', 'Portuguese' => 'Portuguese', 'Japanese' => 'Japanese', 'Arabic' => 'Arabic'], 'default' => 'Spanish'],
                    ['name' => 'text_input', 'label' => 'Or Paste Text to Translate', 'type' => 'textarea', 'placeholder' => 'Paste document text here...'],
                ],
            ],

            // ==========================================
            // D. AI-POWERED TOOLS (9 Tools)
            // ==========================================
            [
                'cat' => 'ai', 'name' => 'AI PDF Summarizer', 'slug' => 'ai-pdf-summarizer', 'icon' => 'bi-Sparkles', 'color' => '#7C3AED',
                'engine' => 'ai', 'action' => 'summarize_pdf', 'input' => 'form_or_file', 'formats' => ['pdf', 'txt'], 'out' => 'pdf',
                'featured' => true, 'popular' => true, 'new' => true,
                'short' => 'Distill long PDF reports, contracts, and research papers into executive bullet summaries.',
                'options' => [
                    ['name' => 'summary_length', 'label' => 'Summary Depth', 'type' => 'select', 'options' => ['concise' => 'Executive Brief (Concise)', 'detailed' => 'Comprehensive Breakdown', 'bullets' => 'Actionable Key Takeaways'], 'default' => 'bullets'],
                    ['name' => 'text_input', 'label' => 'Additional Context / Focus Area (Optional)', 'type' => 'textarea', 'placeholder' => 'Focus on financial metrics or legal obligations...'],
                ],
            ],
            [
                'cat' => 'ai', 'name' => 'AI Document Summarizer', 'slug' => 'ai-document-summarizer', 'icon' => 'bi-journal-text', 'color' => '#6D28D9',
                'engine' => 'ai', 'action' => 'summarize_document', 'input' => 'form_or_file', 'formats' => ['txt', 'md', 'pdf', 'doc', 'docx'], 'out' => 'pdf',
                'short' => 'Generate structured executive summaries from any uploaded document or pasted text.',
                'options' => [
                    ['name' => 'text_input', 'label' => 'Paste Document Text (or upload file above)', 'type' => 'textarea', 'placeholder' => 'Paste article, report, or meeting notes...'],
                ],
            ],
            [
                'cat' => 'ai', 'name' => 'AI PDF Question Answering', 'slug' => 'ai-pdf-question-answering', 'icon' => 'bi-chat-square-quote-fill', 'color' => '#8B5CF6',
                'engine' => 'ai', 'action' => 'pdf_qa', 'input' => 'form_or_file', 'formats' => ['pdf', 'txt'], 'out' => 'pdf',
                'featured' => true, 'new' => true,
                'short' => 'Ask specific questions about your PDF document and receive cited, structured answers.',
                'options' => [
                    ['name' => 'question', 'label' => 'Your Question About the Document', 'type' => 'text', 'default' => 'What are the main conclusions and action items?', 'placeholder' => 'e.g., What are the termination clauses?'],
                    ['name' => 'text_input', 'label' => 'Optional Document Excerpt', 'type' => 'textarea', 'placeholder' => 'Or paste text directly...'],
                ],
            ],
            [
                'cat' => 'ai', 'name' => 'AI Text Rewriter', 'slug' => 'ai-text-rewriter', 'icon' => 'bi-Vector-pen', 'color' => '#4F46E5',
                'engine' => 'ai', 'action' => 'rewrite_text', 'input' => 'form_or_file', 'formats' => ['txt', 'pdf', 'md'], 'out' => 'txt',
                'popular' => true,
                'short' => 'Rewrite paragraphs and documents for clarity, executive tone, persuasive impact, or conciseness.',
                'options' => [
                    ['name' => 'tone', 'label' => 'Desired Tone', 'type' => 'select', 'options' => ['professional' => 'Executive & Professional', 'persuasive' => 'Persuasive & Engaging', 'academic' => 'Academic & Formal', 'simple' => 'Clear & Accessible'], 'default' => 'professional'],
                    ['name' => 'text_input', 'label' => 'Text to Rewrite', 'type' => 'textarea', 'default' => 'Our team worked on the quarterly deliverables and achieved strong growth across key customer segments.'],
                ],
            ],
            [
                'cat' => 'ai', 'name' => 'AI Grammar Checker', 'slug' => 'ai-grammar-checker', 'icon' => 'bi-spellcheck', 'color' => '#10B981',
                'engine' => 'ai', 'action' => 'grammar_check', 'input' => 'form_or_file', 'formats' => ['txt', 'md', 'pdf'], 'out' => 'txt',
                'popular' => true,
                'short' => 'Detect and fix grammar, punctuation, syntax, and style issues with detailed explanations.',
                'options' => [
                    ['name' => 'text_input', 'label' => 'Text to Proofread', 'type' => 'textarea', 'default' => 'Their going to finalized the contract tomorrow morning and sends the updated schedule to every stakeholders.'],
                ],
            ],
            [
                'cat' => 'ai', 'name' => 'AI Paraphrasing Tool', 'slug' => 'ai-paraphrasing-tool', 'icon' => 'bi-arrow-left-right', 'color' => '#0EA5E9',
                'engine' => 'ai', 'action' => 'paraphrase_text', 'input' => 'form_or_file', 'formats' => ['txt', 'md', 'pdf'], 'out' => 'txt',
                'short' => 'Rephrase sentences and articles with fresh vocabulary while preserving original meaning.',
                'options' => [
                    ['name' => 'text_input', 'label' => 'Text to Paraphrase', 'type' => 'textarea', 'default' => 'Digital transformation enables modern enterprises to automate repetitive document workflows and improve operational efficiency.'],
                ],
            ],
            [
                'cat' => 'ai', 'name' => 'AI Document Translator', 'slug' => 'ai-document-translator', 'icon' => 'bi-globe2', 'color' => '#2563EB',
                'engine' => 'ai', 'action' => 'translate_document', 'input' => 'form_or_file', 'formats' => ['txt', 'pdf', 'md'], 'out' => 'pdf',
                'short' => 'Context-aware neural translation for business, legal, and technical documents.',
                'options' => [
                    ['name' => 'target_language', 'label' => 'Translate Into', 'type' => 'select', 'options' => ['Spanish' => 'Spanish', 'French' => 'French', 'German' => 'German', 'Italian' => 'Italian', 'Japanese' => 'Japanese'], 'default' => 'Spanish'],
                    ['name' => 'text_input', 'label' => 'Text to Translate', 'type' => 'textarea', 'default' => 'Welcome to Doctix. Your enterprise documents are processed securely with full encryption.'],
                ],
            ],
            [
                'cat' => 'ai', 'name' => 'AI Content Generator', 'slug' => 'ai-content-generator', 'icon' => 'bi-lightbulb-fill', 'color' => '#F59E0B',
                'engine' => 'ai', 'action' => 'generate_content', 'input' => 'form_or_file', 'formats' => ['txt'], 'out' => 'pdf',
                'short' => 'Generate structured business proposals, blog articles, SOPs, and executive memos in PDF.',
                'options' => [
                    ['name' => 'topic', 'label' => 'Content Topic / Brief', 'type' => 'text', 'default' => 'SaaS Cloud Security Best Practices for 2026'],
                    ['name' => 'content_type', 'label' => 'Document Format', 'type' => 'select', 'options' => ['article' => 'Comprehensive Article', 'proposal' => 'Business Proposal', 'memo' => 'Executive Memo', 'sop' => 'Standard Operating Procedure'], 'default' => 'proposal'],
                ],
            ],
            [
                'cat' => 'ai', 'name' => 'AI Resume Builder', 'slug' => 'ai-resume-builder', 'icon' => 'bi-person-vcard-fill', 'color' => '#EC4899',
                'engine' => 'ai', 'action' => 'build_resume', 'input' => 'form_or_file', 'formats' => ['txt', 'pdf'], 'out' => 'pdf',
                'featured' => true, 'new' => true,
                'short' => 'Generate a polished, ATS-optimized professional resume PDF from your career highlights.',
                'options' => [
                    ['name' => 'candidate_name', 'label' => 'Full Name', 'type' => 'text', 'default' => 'Jordan Taylor'],
                    ['name' => 'target_role', 'label' => 'Target Role / Title', 'type' => 'text', 'default' => 'Senior Cloud Solutions Architect'],
                    ['name' => 'text_input', 'label' => 'Experience & Skills Summary', 'type' => 'textarea', 'default' => "8+ years building scalable SaaS platforms in PHP/Laravel, AWS, MySQL, and Redis.\nLed engineering team of 12 developers; improved system throughput by 340%."],
                ],
            ],

            // ==========================================
            // E. ADDITIONAL PRODUCTIVITY TOOLS (10 Tools)
            // ==========================================
            [
                'cat' => 'utility', 'name' => 'QR Code Generator', 'slug' => 'qr-code-generator', 'icon' => 'bi-qr-code', 'color' => '#0F172A',
                'engine' => 'utility', 'action' => 'qr_code', 'input' => 'form_or_file', 'formats' => ['txt'], 'out' => 'svg',
                'featured' => true, 'popular' => true,
                'short' => 'Create crisp, scalable vector QR codes for URLs, Wi-Fi credentials, vCards, and text.',
                'options' => [
                    ['name' => 'text_input', 'label' => 'URL or Text Content for QR Code', 'type' => 'text', 'default' => 'https://doctix.io'],
                ],
            ],
            [
                'cat' => 'utility', 'name' => 'Barcode Generator', 'slug' => 'barcode-generator', 'icon' => 'bi-upc-scan', 'color' => '#334155',
                'engine' => 'utility', 'action' => 'barcode', 'input' => 'form_or_file', 'formats' => ['txt'], 'out' => 'svg',
                'short' => 'Generate scannable Code-128 vector barcodes for inventory, SKU labels, and asset tags.',
                'options' => [
                    ['name' => 'text_input', 'label' => 'Barcode Value (Alphanumeric)', 'type' => 'text', 'default' => 'DOCTIX-2026-PRO'],
                ],
            ],
            [
                'cat' => 'utility', 'name' => 'Word Counter', 'slug' => 'word-counter', 'icon' => 'bi-calculator-fill', 'color' => '#2563EB',
                'engine' => 'utility', 'action' => 'word_counter', 'input' => 'form_or_file', 'formats' => ['txt', 'md', 'pdf'], 'out' => 'txt',
                'popular' => true,
                'short' => 'Analyze word count, sentence count, paragraph density, reading time, and keyword frequency.',
                'options' => [
                    ['name' => 'text_input', 'label' => 'Paste Text (or upload a file)', 'type' => 'textarea', 'default' => 'Doctix provides fast, reliable document conversion and PDF editing tools for modern teams.'],
                ],
            ],
            [
                'cat' => 'utility', 'name' => 'Character Counter', 'slug' => 'character-counter', 'icon' => 'bi-type', 'color' => '#4F46E5',
                'engine' => 'utility', 'action' => 'character_counter', 'input' => 'form_or_file', 'formats' => ['txt', 'md'], 'out' => 'txt',
                'short' => 'Count characters with and without spaces, bytes, lines, and social media length limits.',
                'options' => [
                    ['name' => 'text_input', 'label' => 'Paste Text to Analyze', 'type' => 'textarea', 'default' => 'Check exact character counts for SEO meta descriptions, tweets, and SMS campaigns.'],
                ],
            ],
            [
                'cat' => 'utility', 'name' => 'Image to Text', 'slug' => 'image-to-text', 'icon' => 'bi-file-earmark-font-fill', 'color' => '#0D9488',
                'engine' => 'utility', 'action' => 'image_to_text', 'input' => 'file', 'formats' => ['jpg', 'jpeg', 'png', 'webp'], 'out' => 'txt',
                'short' => 'Extract readable text and metadata from uploaded images and scanned document photos.',
            ],
            [
                'cat' => 'utility', 'name' => 'Text to PDF', 'slug' => 'text-to-pdf', 'icon' => 'bi-file-earmark-pdf-fill', 'color' => '#E11D48',
                'engine' => 'utility', 'action' => 'text_to_pdf', 'input' => 'form_or_file', 'formats' => ['txt', 'md'], 'out' => 'pdf',
                'popular' => true,
                'short' => 'Write or paste any text and generate a formatted, downloadable PDF document immediately.',
                'options' => [
                    ['name' => 'title', 'label' => 'Document Header Title', 'type' => 'text', 'default' => 'Executive Notes'],
                    ['name' => 'text_input', 'label' => 'Document Body Text', 'type' => 'textarea', 'default' => "1. Project Overview\nDoctix converts plain notes into polished PDF documents automatically."],
                ],
            ],
            [
                'cat' => 'utility', 'name' => 'CSV to Excel', 'slug' => 'csv-to-excel', 'icon' => 'bi-file-earmark-spreadsheet-fill', 'color' => '#16A34A',
                'engine' => 'utility', 'action' => 'csv_to_excel', 'input' => 'form_or_file', 'formats' => ['csv', 'txt'], 'out' => 'xls',
                'short' => 'Convert comma-separated CSV files into formatted Excel XML spreadsheet workbooks.',
            ],
            [
                'cat' => 'utility', 'name' => 'JSON Formatter', 'slug' => 'json-formatter', 'icon' => 'bi-braces', 'color' => '#D97706',
                'engine' => 'utility', 'action' => 'json_formatter', 'input' => 'form_or_file', 'formats' => ['json', 'txt'], 'out' => 'json',
                'popular' => true,
                'short' => 'Validate, prettify, or minify JSON payloads with syntax verification and key sorting.',
                'options' => [
                    ['name' => 'mode', 'label' => 'Formatting Mode', 'type' => 'select', 'options' => ['pretty' => 'Prettify (4-Space Indent)', 'minify' => 'Minify (Compact)', 'sort' => 'Sort Keys Alphabetically'], 'default' => 'pretty'],
                    ['name' => 'text_input', 'label' => 'JSON Input', 'type' => 'textarea', 'default' => '{"platform":"Doctix","version":"2.0","features":["PDF","Image","AI"],"active":true}'],
                ],
            ],
            [
                'cat' => 'utility', 'name' => 'Base64 Encoder/Decoder', 'slug' => 'base64-encoder-decoder', 'icon' => 'bi-shield-slash-fill', 'color' => '#6366F1',
                'engine' => 'utility', 'action' => 'base64_tool', 'input' => 'form_or_file', 'formats' => ['txt', 'bin', 'pdf', 'png', 'jpg'], 'out' => 'txt',
                'short' => 'Encode files or strings to Base64 or decode Base64 payloads back to original text.',
                'options' => [
                    ['name' => 'direction', 'label' => 'Operation', 'type' => 'select', 'options' => ['encode' => 'Encode to Base64', 'decode' => 'Decode from Base64'], 'default' => 'encode'],
                    ['name' => 'text_input', 'label' => 'Text or Base64 String', 'type' => 'textarea', 'default' => 'Doctix Enterprise SaaS Platform'],
                ],
            ],
            [
                'cat' => 'utility', 'name' => 'Markdown to PDF', 'slug' => 'markdown-to-pdf', 'icon' => 'bi-markdown-fill', 'color' => '#0F172A',
                'engine' => 'utility', 'action' => 'markdown_to_pdf', 'input' => 'form_or_file', 'formats' => ['md', 'txt'], 'out' => 'pdf',
                'featured' => true,
                'short' => 'Compile Markdown documentation, READMEs, and technical notes into styled PDF files.',
                'options' => [
                    ['name' => 'text_input', 'label' => 'Markdown Source', 'type' => 'textarea', 'default' => "# Doctix Technical Specification\n\n## Key Capabilities\n- High-speed PDF manipulation\n- Automated daily quota tracking\n- Enterprise encryption"],
                ],
            ],
        ];

        foreach ($tools as $index => $item) {
            $category = $categories[$item['cat']];
            Tool::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'tool_category_id' => $category->id,
                    'name' => $item['name'],
                    'short_description' => $item['short'],
                    'description' => $item['short'] . ' Built on Doctix\'s high-speed cloud processing engine with bank-grade TLS encryption, automatic temporary file cleanup, and instant download delivery.',
                    'icon' => $item['icon'],
                    'accent_color' => $item['color'],
                    'engine_driver' => $item['engine'],
                    'handler_action' => $item['action'],
                    'input_mode' => $item['input'],
                    'accepted_formats' => $item['formats'],
                    'output_format' => $item['out'],
                    'free_max_file_size_mb' => 25,
                    'premium_max_file_size_mb' => 250,
                    'max_files' => $item['input'] === 'multi_file' ? 20 : 1,
                    'daily_free_limit' => 4,
                    'is_premium_only' => false,
                    'allow_guest' => true,
                    'is_active' => true,
                    'is_featured' => $item['featured'] ?? false,
                    'is_popular' => $item['popular'] ?? false,
                    'is_new' => $item['new'] ?? false,
                    'availability_status' => 'available',
                    'queue_processing' => false,
                    'sort_order' => $index + 1,
                    'instructions' => [
                        'Upload your file using the drag-and-drop zone or configure the input options.',
                        'Customize any tool-specific parameters such as compression level, rotation angle, or target format.',
                        'Click "Process Now" — Doctix processes your request securely in the cloud.',
                        'Download your processed result immediately via a secure signed link.',
                    ],
                    'faqs' => [
                        [
                            'q' => 'How many times can I use ' . $item['name'] . ' for free?',
                            'a' => 'Free accounts can use ' . $item['name'] . ' up to 4 times per calendar day. Your allowance for this tool is completely independent of other tools and resets automatically at midnight.',
                        ],
                        [
                            'q' => 'Are my uploaded files secure when using ' . $item['name'] . '?',
                            'a' => 'Yes. All files are stored in isolated private storage, accessible only to you via signed tokens, and automatically purged after 24 hours.',
                        ],
                        [
                            'q' => 'How can I unlock unlimited usage and 250 MB file uploads?',
                            'a' => 'Upgrade to Doctix Monthly or Annual Premium at any time from the Pricing page for unlimited daily operations across all 60+ tools.',
                        ],
                    ],
                    'options_schema' => $item['options'] ?? [],
                    'seo_title' => $item['name'] . ' Online — Fast, Free & Secure | Doctix',
                    'seo_description' => $item['short'] . ' Use ' . $item['name'] . ' free up to 4 times daily on Doctix.',
                    'seo_keywords' => strtolower($item['name']) . ', online pdf tools, doctix, document converter',
                ]
            );
        }
    }
}

