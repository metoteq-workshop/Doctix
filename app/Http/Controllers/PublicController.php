<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Faq;
use App\Models\NewsletterSubscriber;
use App\Models\SubscriptionPlan;
use App\Models\SupportTicket;
use App\Models\Testimonial;
use App\Models\Tool;
use App\Models\ToolCategory;
use App\Services\UsageEnforcementService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicController extends Controller
{
    public function index(UsageEnforcementService $usageService)
    {
        $categories = ToolCategory::with(['activeTools'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $popularTools = Tool::with('category')
            ->where('is_active', true)
            ->where('is_popular', true)
            ->orderBy('sort_order')
            ->take(12)
            ->get();

        $featuredTools = Tool::with('category')
            ->where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->take(8)
            ->get();

        $plans = SubscriptionPlan::with('features')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $testimonials = Testimonial::where('is_active', true)
            ->orderBy('sort_order')
            ->take(6)
            ->get();

        $faqs = Faq::where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->take(8)
            ->get();

        $blogPosts = BlogPost::with(['category', 'author'])
            ->where('is_published', true)
            ->latest('published_at')
            ->take(3)
            ->get();

        $totalToolsCount = Tool::where('is_active', true)->count();

        return view('public.home', compact(
            'categories',
            'popularTools',
            'featuredTools',
            'plans',
            'testimonials',
            'faqs',
            'blogPosts',
            'totalToolsCount'
        ));
    }

    public function pricing()
    {
        $plans = SubscriptionPlan::with('features')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $faqs = Faq::where('is_active', true)
            ->whereIn('category', ['Billing', 'General'])
            ->orderBy('sort_order')
            ->get();

        return view('public.pricing', compact('plans', 'faqs'));
    }

    public function features()
    {
        $categories = ToolCategory::with('activeTools')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('public.features', compact('categories'));
    }

    public function about()
    {
        $totalTools = Tool::where('is_active', true)->count();
        $testimonials = Testimonial::where('is_active', true)->take(3)->get();
        return view('public.about', compact('totalTools', 'testimonials'));
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:150',
            'subject' => 'required|string|max:200',
            'category' => 'nullable|string|max:50',
            'message' => 'required|string|max:4000',
        ]);

        $ticket = SupportTicket::create([
            'ticket_number' => 'TKT-' . strtoupper(Str::random(6)),
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'category' => $validated['category'] ?? 'general',
            'priority' => 'normal',
            'status' => 'open',
            'message' => $validated['message'],
        ]);

        return back()->with('success', "Thank you! Your inquiry has been logged under Ticket #{$ticket->ticket_number}. Our team will respond shortly.");
    }

    public function blog(Request $request)
    {
        $categories = BlogCategory::where('is_active', true)->get();
        $query = BlogPost::with(['category', 'author'])->where('is_published', true);

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->string('category')));
        }

        $posts = $query->latest('published_at')->paginate(9);
        return view('public.blog', compact('posts', 'categories'));
    }

    public function blogPost(string $slug)
    {
        $post = BlogPost::with(['category', 'author'])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $post->increment('views_count');
        $relatedPosts = BlogPost::where('is_published', true)
            ->where('id', '!=', $post->id)
            ->take(3)
            ->get();

        return view('public.blog-details', compact('post', 'relatedPosts'));
    }

    public function helpCenter()
    {
        $faqs = Faq::where('is_active', true)->orderBy('sort_order')->get()->groupBy('category');
        $categories = ToolCategory::where('is_active', true)->orderBy('sort_order')->get();
        return view('public.help-center', compact('faqs', 'categories'));
    }

    public function faq()
    {
        $faqs = Faq::where('is_active', true)->orderBy('sort_order')->get();
        return view('public.faq', compact('faqs'));
    }

    public function legal(string $page)
    {
        $titles = [
            'privacy-policy' => 'Privacy Policy & Data Protection',
            'terms-and-conditions' => 'Terms and Conditions of Service',
            'cookie-policy' => 'Cookie Policy',
            'refund-policy' => 'Subscription Refund & Cancellation Policy',
        ];

        $title = $titles[$page] ?? abort(404);
        return view('public.legal', compact('page', 'title'));
    }

    public function subscribeNewsletter(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:150',
        ]);

        NewsletterSubscriber::updateOrCreate(
            ['email' => strtolower($validated['email'])],
            ['status' => 'subscribed', 'ip_address' => $request->ip(), 'subscribed_at' => now()]
        );

        return back()->with('success', 'You are subscribed to the Doctix product updates newsletter!');
    }

    public function sitemapXml()
    {
        $tools = Tool::where('is_active', true)->get(['slug', 'updated_at']);
        $posts = BlogPost::where('is_published', true)->get(['slug', 'updated_at']);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $staticUrls = ['/', '/tools', '/pricing', '/features', '/about', '/contact', '/blog', '/help-center', '/faq'];
        foreach ($staticUrls as $path) {
            $xml .= '  <url><loc>' . url($path) . '</loc><changefreq>daily</changefreq><priority>0.9</priority></url>' . "\n";
        }

        foreach ($tools as $tool) {
            $xml .= '  <url><loc>' . route('tools.show', $tool->slug) . '</loc><changefreq>weekly</changefreq><priority>0.8</priority></url>' . "\n";
        }

        foreach ($posts as $post) {
            $xml .= '  <url><loc>' . route('blog.show', $post->slug) . '</loc><changefreq>weekly</changefreq><priority>0.7</priority></url>' . "\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function robotsTxt()
    {
        $content = "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /dashboard/\nDisallow: /files/\nSitemap: " . url('/sitemap.xml') . "\n";
        return response($content, 200, ['Content-Type' => 'text/plain']);
    }
}

