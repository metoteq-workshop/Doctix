<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Coupon;
use App\Models\EmailTemplate;
use App\Models\Faq;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\Permission;
use App\Models\PlanFeature;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\SupportTicket;
use App\Models\SystemSetting;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles & Permissions
        $adminRole = Role::updateOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Administrator', 'description' => 'Full platform administrative access', 'is_system' => true]
        );
        $managerRole = Role::updateOrCreate(
            ['slug' => 'manager'],
            ['name' => 'Operations Manager', 'description' => 'Manage tools, CMS, and support', 'is_system' => true]
        );
        $userRole = Role::updateOrCreate(
            ['slug' => 'user'],
            ['name' => 'Standard User', 'description' => 'Registered customer account', 'is_system' => true]
        );

        $permissions = [
            ['slug' => 'manage-users', 'name' => 'Manage Users', 'group' => 'users'],
            ['slug' => 'manage-tools', 'name' => 'Manage Tools', 'group' => 'tools'],
            ['slug' => 'manage-subscriptions', 'name' => 'Manage Subscriptions', 'group' => 'billing'],
            ['slug' => 'manage-payments', 'name' => 'Manage Payments & Refunds', 'group' => 'billing'],
            ['slug' => 'manage-cms', 'name' => 'Manage CMS Content', 'group' => 'cms'],
            ['slug' => 'manage-settings', 'name' => 'Manage System Settings', 'group' => 'system'],
            ['slug' => 'reset-user-usage', 'name' => 'Reset Daily User Usage', 'group' => 'usage'],
        ];

        $permIds = [];
        foreach ($permissions as $perm) {
            $p = Permission::updateOrCreate(['slug' => $perm['slug']], $perm);
            $permIds[] = $p->id;
        }
        $adminRole->permissions()->sync($permIds);

        // 2. Demo & Admin Users
        $admin = User::updateOrCreate(
            ['email' => 'admin@doctix.io'],
            [
                'name' => 'Sophia Sterling (Admin)',
                'password' => Hash::make('Password123!'),
                'email_verified_at' => now(),
                'role' => 'admin',
                'status' => 'active',
                'unlimited_override' => true,
            ]
        );
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);
        UserProfile::updateOrCreate(['user_id' => $admin->id], [
            'company' => 'Doctix Cloud Inc.',
            'job_title' => 'Chief Platform Architect',
            'country' => 'United States',
            'timezone' => 'UTC',
        ]);

        $premiumUser = User::updateOrCreate(
            ['email' => 'premium@doctix.io'],
            [
                'name' => 'Marcus Vance (Premium)',
                'password' => Hash::make('Password123!'),
                'email_verified_at' => now(),
                'role' => 'user',
                'status' => 'active',
            ]
        );
        $premiumUser->roles()->syncWithoutDetaching([$userRole->id]);
        UserProfile::updateOrCreate(['user_id' => $premiumUser->id], [
            'company' => 'Vance Legal Partners',
            'job_title' => 'Managing Partner',
            'country' => 'United Kingdom',
            'timezone' => 'UTC',
        ]);

        $freeUser = User::updateOrCreate(
            ['email' => 'free@doctix.io'],
            [
                'name' => 'Elena Rostova (Free Plan)',
                'password' => Hash::make('Password123!'),
                'email_verified_at' => now(),
                'role' => 'user',
                'status' => 'active',
            ]
        );
        $freeUser->roles()->syncWithoutDetaching([$userRole->id]);
        UserProfile::updateOrCreate(['user_id' => $freeUser->id], [
            'company' => 'Rostova Design Studio',
            'job_title' => 'Product Designer',
            'country' => 'Canada',
            'timezone' => 'UTC',
        ]);

        // 3. Subscription Plans
        $freePlan = SubscriptionPlan::updateOrCreate(
            ['slug' => 'free'],
            [
                'name' => 'Free Plan',
                'badge_text' => 'Starter',
                'description' => 'Essential daily productivity for individuals. Use every individual tool up to 4 times per day.',
                'price' => 0.00,
                'currency' => 'USD',
                'billing_interval' => 'free',
                'interval_days' => 3650,
                'is_unlimited' => false,
                'daily_tool_limit' => 4,
                'max_file_size_mb' => 25,
                'priority_processing' => false,
                'access_premium_tools' => false,
                'access_ai_tools' => true,
                'grace_period_days' => 0,
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $monthlyPlan = SubscriptionPlan::updateOrCreate(
            ['slug' => 'monthly-premium'],
            [
                'name' => 'Monthly Premium',
                'badge_text' => 'Most Popular',
                'description' => 'Unlimited daily access across all 60+ PDF, Image, Document, and AI tools with priority cloud queues.',
                'price' => 19.00,
                'currency' => 'USD',
                'billing_interval' => 'monthly',
                'interval_days' => 30,
                'is_unlimited' => true,
                'daily_tool_limit' => 0,
                'max_file_size_mb' => 250,
                'priority_processing' => true,
                'access_premium_tools' => true,
                'access_ai_tools' => true,
                'grace_period_days' => 3,
                'is_featured' => true,
                'is_active' => true,
                'sort_order' => 2,
                'stripe_price_id' => 'price_doctix_monthly_19',
                'paypal_plan_id' => 'P-DOCTIX-MONTHLY-19',
                'paystack_plan_code' => 'PLN_doctix_monthly',
                'flutterwave_plan_id' => 'FLW_doctix_monthly',
            ]
        );

        $annualPlan = SubscriptionPlan::updateOrCreate(
            ['slug' => 'annual-premium'],
            [
                'name' => 'Annual Premium',
                'badge_text' => 'Save 35%',
                'description' => 'All Monthly Premium benefits billed annually at a 35% discount for professionals and power teams.',
                'price' => 149.00,
                'currency' => 'USD',
                'billing_interval' => 'annual',
                'interval_days' => 365,
                'is_unlimited' => true,
                'daily_tool_limit' => 0,
                'max_file_size_mb' => 500,
                'priority_processing' => true,
                'access_premium_tools' => true,
                'access_ai_tools' => true,
                'grace_period_days' => 7,
                'is_featured' => false,
                'is_active' => true,
                'sort_order' => 3,
                'stripe_price_id' => 'price_doctix_annual_149',
                'paypal_plan_id' => 'P-DOCTIX-ANNUAL-149',
                'paystack_plan_code' => 'PLN_doctix_annual',
                'flutterwave_plan_id' => 'FLW_doctix_annual',
            ]
        );

        $planFeaturesMap = [
            $freePlan->id => [
                ['4 uses per individual tool per day', '4 / tool / day', true],
                ['Independent quota per tool (resets at midnight)', 'Included', true],
                ['Maximum upload file size', '25 MB', true],
                ['Standard processing queue', 'Standard', true],
                ['Unlimited daily operations', 'Not included', false],
                ['Priority high-speed processing & batch mode', 'Not included', false],
            ],
            $monthlyPlan->id => [
                ['Unlimited daily usage across all 60+ tools', 'Unlimited', true],
                ['No daily quota restrictions on any tool', 'Unlimited', true],
                ['Maximum upload file size', '250 MB', true],
                ['Priority cloud processing queue', 'Priority', true],
                ['Full AI PDF Summarizer, Q&A & Translator suite', 'Included', true],
                ['Ad-free workspace & automated invoice billing', 'Included', true],
            ],
            $annualPlan->id => [
                ['Unlimited daily usage across all 60+ tools', 'Unlimited', true],
                ['No daily quota restrictions on any tool', 'Unlimited', true],
                ['Extended maximum upload file size', '500 MB', true],
                ['Highest priority cloud processing queue', 'VIP Priority', true],
                ['Full AI PDF Summarizer, Q&A & Translator suite', 'Included', true],
                ['35% annual billing discount + 7-day grace period', 'Included', true],
            ],
        ];

        foreach ($planFeaturesMap as $planId => $features) {
            PlanFeature::where('subscription_plan_id', $planId)->delete();
            foreach ($features as $i => $feat) {
                PlanFeature::create([
                    'subscription_plan_id' => $planId,
                    'feature_name' => $feat[0],
                    'feature_value' => $feat[1],
                    'is_included' => $feat[2],
                    'sort_order' => $i + 1,
                ]);
            }
        }

        // 4. Active Subscription for Demo Premium User
        $activeSub = Subscription::updateOrCreate(
            ['user_id' => $premiumUser->id, 'subscription_plan_id' => $monthlyPlan->id],
            [
                'subscription_uuid' => (string) Str::uuid(),
                'status' => 'active',
                'payment_gateway' => 'stripe',
                'gateway_subscription_id' => 'sub_demo_stripe_001',
                'amount' => 19.00,
                'currency' => 'USD',
                'auto_renew' => true,
                'starts_at' => now()->subDays(5),
                'ends_at' => now()->addDays(25),
                'grace_ends_at' => now()->addDays(28),
            ]
        );

        $demoPayment = Payment::updateOrCreate(
            ['transaction_reference' => 'DTX-DEMO-10001'],
            [
                'user_id' => $premiumUser->id,
                'subscription_id' => $activeSub->id,
                'subscription_plan_id' => $monthlyPlan->id,
                'gateway' => 'stripe',
                'gateway_transaction_id' => 'pi_demo_998877',
                'subtotal' => 19.00,
                'discount_amount' => 0.00,
                'tax_amount' => 0.00,
                'amount' => 19.00,
                'currency' => 'USD',
                'status' => 'succeeded',
                'payment_method_type' => 'card',
                'card_brand' => 'Visa',
                'card_last4' => '4242',
                'webhook_verified' => true,
                'paid_at' => now()->subDays(5),
            ]
        );

        Invoice::updateOrCreate(
            ['invoice_number' => 'INV-2026-0001'],
            [
                'user_id' => $premiumUser->id,
                'subscription_id' => $activeSub->id,
                'payment_id' => $demoPayment->id,
                'subtotal' => 19.00,
                'discount' => 0.00,
                'tax' => 0.00,
                'total' => 19.00,
                'currency' => 'USD',
                'status' => 'paid',
                'billing_name' => $premiumUser->name,
                'billing_email' => $premiumUser->email,
                'billing_address' => '100 Bishopsgate, London, UK',
                'issued_at' => now()->subDays(5),
                'paid_at' => now()->subDays(5),
            ]
        );

        // 5. Payment Gateways
        $gateways = [
            ['code' => 'stripe', 'name' => 'Stripe', 'display_name' => 'Credit / Debit Card (Stripe)', 'description' => 'Pay securely with Visa, Mastercard, Amex, Apple Pay, or Google Pay.', 'currencies' => ['USD', 'EUR', 'GBP'], 'sort' => 1],
            ['code' => 'paypal', 'name' => 'PayPal', 'display_name' => 'PayPal Express & Subscriptions', 'description' => 'Fast checkout with your PayPal balance or linked bank account.', 'currencies' => ['USD', 'EUR', 'GBP'], 'sort' => 2],
            ['code' => 'paystack', 'name' => 'Paystack', 'display_name' => 'Paystack (Card, Bank, USSD)', 'description' => 'Seamless African & international card and bank transfer payments.', 'currencies' => ['USD', 'NGN', 'GHS', 'ZAR', 'KES'], 'sort' => 3],
            ['code' => 'flutterwave', 'name' => 'Flutterwave', 'display_name' => 'Flutterwave Global Checkout', 'description' => 'Multi-currency cards, mobile money, and bank transfers.', 'currencies' => ['USD', 'EUR', 'GBP', 'NGN', 'KES'], 'sort' => 4],
        ];
        foreach ($gateways as $gw) {
            PaymentGateway::updateOrCreate(
                ['code' => $gw['code']],
                [
                    'name' => $gw['name'],
                    'display_name' => $gw['display_name'],
                    'description' => $gw['description'],
                    'is_enabled' => true,
                    'is_sandbox' => true,
                    'public_key' => 'pk_test_' . $gw['code'] . '_doctix',
                    'secret_key' => encrypt('sk_test_' . $gw['code'] . '_secret'),
                    'webhook_secret' => encrypt('whsec_' . $gw['code'] . '_secret'),
                    'supported_currencies' => $gw['currencies'],
                    'sort_order' => $gw['sort'],
                ]
            );
        }

        // 6. Coupons
        Coupon::updateOrCreate(['code' => 'WELCOME20'], [
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'max_redemptions' => 500,
            'expires_at' => now()->addYear(),
            'is_active' => true,
        ]);

        // 7. System Settings
        $defaultSettings = [
            ['general', 'site_name', 'Doctix', 'string', true],
            ['general', 'site_tagline', 'All-in-One Online PDF, Image, Document & AI Productivity Suite', 'string', true],
            ['general', 'support_email', 'support@doctix.io', 'string', true],
            ['general', 'hero_headline', 'Every Document Tool You Need, Engineered for Speed.', 'string', true],
            ['general', 'hero_subheadline', 'Merge, compress, convert, sign, and analyze PDFs, images, and Office documents right in your browser. Use every individual tool 4 times daily for free — or unlock unlimited power with Premium.', 'string', true],
            ['limits', 'daily_free_limit', '4', 'integer', true],
            ['limits', 'allow_guest_tools', '1', 'boolean', true],
            ['limits', 'guest_daily_limit', '2', 'integer', true],
            ['limits', 'free_max_file_size_mb', '25', 'integer', true],
            ['limits', 'premium_max_file_size_mb', '250', 'integer', true],
            ['storage', 'temp_file_retention_hours', '24', 'integer', false],
            ['storage', 'active_disk', 'local', 'string', false],
            ['ai', 'ai_enabled', '1', 'boolean', false],
            ['ai', 'ai_provider', 'openai', 'string', false],
            ['seo', 'meta_title', 'Doctix — Free Online PDF Editor, Converter & AI Document Tools', 'string', true],
            ['seo', 'meta_description', 'Convert, merge, split, compress, and edit PDF files, images, and Office documents online. 4 free daily uses per tool.', 'string', true],
        ];
        foreach ($defaultSettings as $s) {
            SystemSetting::setValue($s[1], $s[2], $s[0], $s[3], $s[4]);
        }

        // 8. Email Templates (All 12 events)
        $templates = [
            ['welcome', 'Welcome Email', 'Welcome to Doctix — Your Document Productivity Suite', '<p>Hi {{name}},</p><p>Welcome to Doctix! You now have 4 free uses per individual tool every single day.</p>'],
            ['email_verification', 'Email Verification', 'Verify Your Doctix Email Address', '<p>Hi {{name}},</p><p>Please verify your email address by clicking the verification link below: {{verification_url}}</p>'],
            ['password_reset', 'Password Reset', 'Reset Your Doctix Password', '<p>Hi {{name}},</p><p>Click here to reset your password: {{reset_url}}</p>'],
            ['subscription_confirmation', 'Subscription Confirmation', 'Your Doctix {{plan_name}} Subscription is Active!', '<p>Hi {{name}},</p><p>Your {{plan_name}} plan is now active. Enjoy unlimited daily tool access!</p>'],
            ['payment_confirmation', 'Payment Confirmation', 'Receipt for Your Doctix Payment ({{reference}})', '<p>Hi {{name}},</p><p>We received your payment of {{amount}} {{currency}}. Invoice: {{invoice_number}}.</p>'],
            ['payment_failure', 'Payment Failure Alert', 'Action Required: Payment Failed for Your Doctix Subscription', '<p>Hi {{name}},</p><p>Your recent payment of {{amount}} could not be processed. Please update your billing method.</p>'],
            ['subscription_renewal_reminder', 'Subscription Renewal Reminder', 'Upcoming Renewal for Your Doctix {{plan_name}} Plan', '<p>Hi {{name}},</p><p>Your subscription will renew on {{renewal_date}}.</p>'],
            ['subscription_expiration', 'Subscription Expiration Notice', 'Your Doctix Premium Subscription Has Expired', '<p>Hi {{name}},</p><p>Your subscription has expired and your account has transitioned to the Free Plan (4 uses/tool/day).</p>'],
            ['subscription_cancellation', 'Subscription Cancellation', 'Confirmation: Your Doctix Subscription Was Canceled', '<p>Hi {{name}},</p><p>Your subscription has been canceled. You will retain access until {{ends_at}}.</p>'],
            ['usage_limit_reached', 'Daily Usage Limit Reached', 'You Reached Today\'s Free Allowance for {{tool_name}}', '<p>Hi {{name}},</p><p>You have used all 4 daily free operations for {{tool_name}} today. Other tools remain available, or upgrade to Premium for unlimited access!</p>'],
            ['processing_completed', 'Processing Completed', 'Your File Processed with {{tool_name}} is Ready', '<p>Hi {{name}},</p><p>Your document has finished processing and is ready to download: {{download_url}}</p>'],
            ['support_ticket_update', 'Support Ticket Update', 'Update on Ticket #{{ticket_number}}: {{subject}}', '<p>Hi {{name}},</p><p>Our support team has replied to your ticket #{{ticket_number}}.</p>'],
        ];
        foreach ($templates as $tpl) {
            EmailTemplate::updateOrCreate(
                ['key' => $tpl[0]],
                [
                    'name' => $tpl[1],
                    'subject' => $tpl[2],
                    'body_html' => $tpl[3],
                    'variables' => ['name', 'plan_name', 'amount', 'currency', 'tool_name'],
                    'is_active' => true,
                ]
            );
        }

        // 9. FAQs, Testimonials & Blog Content
        $faqs = [
            ['General', 'How does the 4 free uses per tool per day allowance work?', 'Every registered free user receives 4 completed operations per individual tool each calendar day. Using Merge PDF 4 times does not reduce your allowance for Compress PDF, PDF to Word, or any other tool. All counters reset automatically at midnight.'],
            ['Billing', 'What happens when I upgrade to Monthly or Annual Premium?', 'As soon as your payment is confirmed on our server, your account unlocks unlimited daily usage across all 60+ tools, up to 250–500 MB file uploads, priority queue execution, and downloadable tax invoices.'],
            ['Security', 'Are my uploaded PDFs and business documents kept private?', 'Yes. All files are stored in non-public encrypted storage, validated against malware/MIME spoofing, accessible exclusively to their owner via signed download tokens, and purged automatically after 24 hours.'],
            ['Tools', 'Can I use Doctix on my smartphone or tablet without installing software?', 'Absolutely. Doctix runs 100% in your mobile or desktop browser with responsive drag-and-drop workspaces.'],
            ['Billing', 'Can I cancel my subscription at any time?', 'Yes. You can cancel with one click from your Subscription dashboard. You will continue to enjoy unlimited Premium access until the end of your current paid billing period.'],
        ];
        foreach ($faqs as $idx => $f) {
            Faq::updateOrCreate(
                ['question' => $f[1]],
                ['category' => $f[0], 'answer' => $f[2], 'is_featured' => true, 'is_active' => true, 'sort_order' => $idx + 1]
            );
        }

        $testimonials = [
            ['Sarah Jenkins', 'VP of Legal Operations', 'AetherLaw Group', 'Doctix replaced three separate desktop PDF utilities for our legal team. The per-tool free quota let us test everything thoroughly before moving our 40 attorneys to Annual Premium.', 5, 'SJ'],
            ['David Chen', 'Founder & Principal Architect', 'Vertex Studio', 'Having Merge PDF, Compress PDF, Image Enhancer, and the AI Contract Summarizer inside one lightning-fast dashboard saves me at least 6 hours every week.', 5, 'DC'],
            ['Amara Okafor', 'Head of Finance', 'FinServe Global', 'Converting bank statements, watermarking confidential audit packs, and signing PDFs in seconds makes Doctix indispensable for our quarterly close.', 5, 'AO'],
        ];
        foreach ($testimonials as $idx => $t) {
            Testimonial::updateOrCreate(
                ['author_name' => $t[0]],
                ['author_role' => $t[1], 'company' => $t[2], 'quote' => $t[3], 'rating' => $t[4], 'avatar_initials' => $t[5], 'is_featured' => true, 'is_active' => true, 'sort_order' => $idx + 1]
            );
        }

        $blogCat1 = BlogCategory::updateOrCreate(['slug' => 'document-productivity'], ['name' => 'Document Productivity', 'description' => 'Workflow guides for PDF and document automation.']);
        $blogCat2 = BlogCategory::updateOrCreate(['slug' => 'ai-workflows'], ['name' => 'AI Workflows', 'description' => 'Leveraging artificial intelligence for contracts and reports.']);

        BlogPost::updateOrCreate(
            ['slug' => 'how-to-compress-pdf-without-losing-quality'],
            [
                'blog_category_id' => $blogCat1->id,
                'author_id' => $admin->id,
                'title' => 'How to Compress Large PDF Files by 75% Without Losing Text Clarity',
                'excerpt' => 'Learn how stream flate compression, font subsetting, and smart image downsampling shrink large PDFs for instant email delivery.',
                'content' => "<p>Large PDF attachments often bounce from corporate mail servers that enforce strict 10 MB or 25 MB limits. With Doctix's <strong>Compress PDF</strong> tool, you can reduce file sizes dramatically while keeping vector typography razor-sharp.</p><h3>1. Stream Re-indexing & Object Cleanup</h3><p>PDF files frequently accumulate duplicate font descriptors and unused cross-reference streams over multiple edits. Doctix reconstructs clean object trees automatically.</p><h3>2. Balanced Downsampling</h3><p>Select 'Recommended Compression' in our workspace to balance high-DPI visual fidelity with compact byte output.</p>",
                'reading_time_minutes' => 4,
                'is_published' => true,
                'is_featured' => true,
                'published_at' => now()->subDays(7),
                'seo_title' => 'How to Compress PDF Files Without Losing Quality | Doctix Blog',
                'seo_description' => 'Step-by-step guide to reducing PDF size for email and web uploads without blurry text.',
            ]
        );

        BlogPost::updateOrCreate(
            ['slug' => 'automating-contract-review-with-ai-pdf-summarizer'],
            [
                'blog_category_id' => $blogCat2->id,
                'author_id' => $admin->id,
                'title' => 'Automating Executive Contract Review with Doctix AI PDF Summarizer',
                'excerpt' => 'Discover how legal and procurement teams extract key obligations, renewal dates, and risk clauses from 80-page PDFs in under 10 seconds.',
                'content' => "<p>Reviewing dense vendor agreements and research reports manually takes hours. Doctix's <strong>AI PDF Summarizer</strong> and <strong>AI PDF Question Answering</strong> tools parse document text layers and synthesize actionable executive briefs immediately.</p><h3>Key Benefits for Teams</h3><ul><li>Instant extraction of deliverables and dates</li><li>Zero data retention in third-party training sets</li><li>Downloadable executive summary PDFs</li></ul>",
                'reading_time_minutes' => 5,
                'is_published' => true,
                'is_featured' => true,
                'published_at' => now()->subDays(3),
                'seo_title' => 'Automate Contract Review with AI PDF Summarizer | Doctix',
                'seo_description' => 'Extract key takeaways and answers from complex PDF documents using Doctix AI.',
            ]
        );

        UserNotification::updateOrCreate(
            ['user_id' => $freeUser->id, 'title' => 'Welcome to Doctix!'],
            [
                'message' => 'You have 4 free operations per individual tool every day. Explore 60+ PDF, Image, Document, and AI tools now.',
                'type' => 'success',
                'action_url' => '/tools',
            ]
        );

        SupportTicket::updateOrCreate(
            ['ticket_number' => 'TKT-1001'],
            [
                'user_id' => $freeUser->id,
                'name' => $freeUser->name,
                'email' => $freeUser->email,
                'subject' => 'Question about team invoice billing for Annual Premium',
                'category' => 'billing',
                'priority' => 'normal',
                'status' => 'open',
                'message' => 'Hi team, does the Annual Premium plan include downloadable VAT/tax invoices for our accounting department?',
            ]
        );

        // 10. Seed All 60+ Tools
        $this->call(ToolsSeeder::class);
    }
}
