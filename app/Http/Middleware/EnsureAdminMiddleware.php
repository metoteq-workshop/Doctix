<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isAdmin()) {
            AuditLog::logEvent(
                module: 'security',
                action: 'unauthorized_admin_access_attempt',
                actorId: $user?->id,
                newValues: ['path' => $request->path(), 'method' => $request->method()],
                severity: 'critical',
                ip: $request->ip()
            );

            abort(403, 'Access Denied: Administrator privileges are required to access this module.');
        }

        return $next($request);
    }
}

