<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\LoginHistory;
use App\Models\Role;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $throttleKey = strtolower($credentials['email']) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ])->onlyInput('email');
        }

        $user = User::where('email', $credentials['email'])->first();

        if ($user && $user->status === 'suspended') {
            return back()->withErrors([
                'email' => 'Your account has been suspended. Please contact support.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            /** @var User $loggedIn */
            $loggedIn = Auth::user();
            $loggedIn->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            LoginHistory::create([
                'user_id' => $loggedIn->id,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 250),
                'device' => $this->detectDevice((string) $request->userAgent()),
                'status' => 'success',
                'logged_in_at' => now(),
            ]);

            ActivityLog::record($loggedIn->id, 'auth.login', 'Signed in to Doctix account', [], $request->ip());

            if ($loggedIn->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended(route('dashboard.index'));
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|string|email|max:150|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'company' => 'nullable|string|max:120',
        ]);

        $user = DB::transaction(function () use ($validated, $request) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => strtolower($validated['email']),
                'password' => Hash::make($validated['password']),
                'email_verified_at' => now(),
                'role' => 'user',
                'status' => 'active',
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            $userRole = Role::where('slug', 'user')->first();
            if ($userRole) {
                $user->roles()->syncWithoutDetaching([$userRole->id]);
            }

            UserProfile::create([
                'user_id' => $user->id,
                'company' => $validated['company'] ?? null,
                'timezone' => config('app.timezone', 'UTC'),
            ]);

            UserNotification::create([
                'user_id' => $user->id,
                'title' => 'Welcome to Doctix!',
                'message' => 'Your free account is active! Enjoy 4 free operations per individual tool every calendar day.',
                'type' => 'success',
                'action_url' => route('tools.index'),
            ]);

            LoginHistory::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 250),
                'device' => $this->detectDevice((string) $request->userAgent()),
                'status' => 'registered',
                'logged_in_at' => now(),
            ]);

            ActivityLog::record($user->id, 'auth.registered', 'Created a new Doctix account', [], $request->ip());

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard.index')->with('success', 'Welcome to Doctix! Your Free Plan (4 uses per tool per day) is ready.');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLog::record(Auth::id(), 'auth.logout', 'Signed out of Doctix', [], $request->ip());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been signed out safely.');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => strtolower($request->string('email'))],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        return back()->with('success', 'If an account exists for that email, a password reset link has been dispatched.');
    }

    protected function detectDevice(string $ua): string
    {
        if (stripos($ua, 'Mobile') !== false || stripos($ua, 'Android') !== false || stripos($ua, 'iPhone') !== false) {
            return 'Mobile / Smartphone';
        }
        if (stripos($ua, 'Windows') !== false) {
            return 'Windows Desktop';
        }
        if (stripos($ua, 'Macintosh') !== false) {
            return 'Mac Desktop';
        }
        return 'Web Browser';
    }
}

