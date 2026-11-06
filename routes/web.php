<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\SubscriptionPaymentController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\UserDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Website & SEO Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/pricing', [PublicController::class, 'pricing'])->name('pricing');
Route::get('/features', [PublicController::class, 'features'])->name('features');
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
Route::post('/contact', [PublicController::class, 'submitContact'])->name('contact.submit');
Route::get('/blog', [PublicController::class, 'blog'])->name('blog.index');
Route::get('/blog/{slug}', [PublicController::class, 'blogPost'])->name('blog.show');
Route::get('/help-center', [PublicController::class, 'helpCenter'])->name('help');
Route::get('/faq', [PublicController::class, 'faq'])->name('faq');

Route::get('/privacy-policy', fn () => app(PublicController::class)->legal('privacy-policy'))->name('legal.privacy');
Route::get('/terms-and-conditions', fn () => app(PublicController::class)->legal('terms-and-conditions'))->name('legal.terms');
Route::get('/cookie-policy', fn () => app(PublicController::class)->legal('cookie-policy'))->name('legal.cookies');
Route::get('/refund-policy', fn () => app(PublicController::class)->legal('refund-policy'))->name('legal.refund');

Route::post('/newsletter/subscribe', [PublicController::class, 'subscribeNewsletter'])->name('newsletter.subscribe');
Route::get('/sitemap.xml', [PublicController::class, 'sitemapXml'])->name('sitemap');
Route::get('/robots.txt', [PublicController::class, 'robotsTxt'])->name('robots');

/*
|--------------------------------------------------------------------------
| Tools Directory, Individual Tool Workspaces & Secure File Downloads
|--------------------------------------------------------------------------
*/
Route::get('/tools', [ToolController::class, 'index'])->name('tools.index');
Route::get('/tools/{tool:slug}', [ToolController::class, 'show'])->name('tools.show');
Route::post('/tools/{tool:slug}/process', [ToolController::class, 'process'])->name('tools.process');
Route::get('/files/download/{token}', [ToolController::class, 'download'])->name('files.download');
Route::get('/files/upload/{uuid}', [ToolController::class, 'viewUpload'])->name('files.upload.view');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Payment Gateway Webhooks (Verified via HMAC Signature)
|--------------------------------------------------------------------------
*/
Route::post('/webhooks/payment/{gateway}', [SubscriptionPaymentController::class, 'webhook'])->name('webhooks.payment');

/*
|--------------------------------------------------------------------------
| Authenticated User Dashboard & Subscription Billing Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [UserDashboardController::class, 'index'])->name('index');
    Route::get('/usage', [UserDashboardController::class, 'usage'])->name('usage');
    Route::get('/documents', [UserDashboardController::class, 'documents'])->name('documents');
    Route::get('/activities', [UserDashboardController::class, 'activities'])->name('activities');
    Route::get('/subscription', [UserDashboardController::class, 'subscription'])->name('subscription');
    Route::get('/invoices/{invoiceNumber}', [UserDashboardController::class, 'showInvoice'])->name('invoice.show');

    Route::get('/profile', [UserDashboardController::class, 'profile'])->name('profile');
    Route::post('/profile', [UserDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [UserDashboardController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/two-factor', [UserDashboardController::class, 'toggleTwoFactor'])->name('profile.2fa');
    Route::post('/profile/sessions/revoke', [UserDashboardController::class, 'revokeSessions'])->name('profile.sessions.revoke');
    Route::delete('/profile/delete', [UserDashboardController::class, 'deleteAccount'])->name('profile.delete');

    Route::get('/notifications', [UserDashboardController::class, 'notifications'])->name('notifications');
    Route::post('/notifications/read-all', [UserDashboardController::class, 'markNotificationsRead'])->name('notifications.read');

    Route::get('/tickets', [UserDashboardController::class, 'tickets'])->name('tickets');
    Route::post('/tickets', [UserDashboardController::class, 'storeTicket'])->name('tickets.store');
    Route::post('/tickets/{ticket}/reply', [UserDashboardController::class, 'replyTicket'])->name('tickets.reply');
});

Route::middleware('auth')->group(function () {
    Route::get('/checkout/{plan:slug}', [SubscriptionPaymentController::class, 'checkout'])->name('checkout.show');
    Route::post('/checkout/{plan:slug}', [SubscriptionPaymentController::class, 'processCheckout'])->name('checkout.process');
    Route::post('/subscription/cancel', [SubscriptionPaymentController::class, 'cancel'])->name('subscription.cancel');
});

/*
|--------------------------------------------------------------------------
| Complete Administration Panel Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/analytics/export-csv', [AdminController::class, 'exportAnalyticsCsv'])->name('analytics.export');

    // User Management
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::post('/users/{user}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('users.toggle');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
    Route::post('/users/{user}/assign-subscription', [AdminController::class, 'assignSubscription'])->name('users.assign-sub');
    Route::post('/users/{user}/reset-usage', [AdminController::class, 'resetUserUsage'])->name('users.reset-usage');

    // Tool Management
    Route::get('/tools', [AdminController::class, 'tools'])->name('tools');
    Route::post('/tools', [AdminController::class, 'storeTool'])->name('tools.store');
    Route::put('/tools/{tool:id}', [AdminController::class, 'updateTool'])->name('tools.update');
    Route::post('/tools/{tool:id}/toggle', [AdminController::class, 'toggleToolStatus'])->name('tools.toggle');
    Route::delete('/tools/{tool:id}', [AdminController::class, 'deleteTool'])->name('tools.delete');

    // Subscription Plans & Subscribers
    Route::get('/plans', [AdminController::class, 'plans'])->name('plans');
    Route::post('/plans', [AdminController::class, 'storePlan'])->name('plans.store');
    Route::put('/plans/{plan}', [AdminController::class, 'updatePlan'])->name('plans.update');
    Route::post('/subscriptions/{subscription}/extend', [AdminController::class, 'extendSubscription'])->name('subscriptions.extend');

    // Payments & Refunds
    Route::get('/payments', [AdminController::class, 'payments'])->name('payments');
    Route::post('/payments/{payment}/refund', [AdminController::class, 'refundPayment'])->name('payments.refund');

    // CMS, Blog, FAQs, Testimonials & Email Templates
    Route::get('/cms', [AdminController::class, 'cms'])->name('cms');
    Route::post('/cms/blog', [AdminController::class, 'storeBlogPost'])->name('cms.blog.store');
    Route::delete('/cms/blog/{post}', [AdminController::class, 'deleteBlogPost'])->name('cms.blog.delete');
    Route::post('/cms/faq', [AdminController::class, 'storeFaq'])->name('cms.faq.store');
    Route::delete('/cms/faq/{faq}', [AdminController::class, 'deleteFaq'])->name('cms.faq.delete');
    Route::post('/cms/testimonial', [AdminController::class, 'storeTestimonial'])->name('cms.testimonial.store');
    Route::put('/cms/email-templates/{template}', [AdminController::class, 'updateEmailTemplate'])->name('cms.email.update');

    // Support Tickets
    Route::get('/tickets', [AdminController::class, 'tickets'])->name('tickets');
    Route::post('/tickets/{ticket}/reply', [AdminController::class, 'replyTicket'])->name('tickets.reply');
    Route::post('/tickets/{ticket}/close', [AdminController::class, 'closeTicket'])->name('tickets.close');

    // System, Gateway & AI Settings
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::put('/settings/gateways/{gateway}', [AdminController::class, 'updateGateway'])->name('settings.gateway.update');

    // Security, Activity & Audit Logs
    Route::get('/logs', [AdminController::class, 'logs'])->name('logs');
    Route::post('/maintenance/run', [AdminController::class, 'runMaintenance'])->name('maintenance.run');
});
