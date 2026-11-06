<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $wantsJson = $request->is('api/*') || $request->expectsJson();

        if (! $user) {
            if ($wantsJson) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route($request->is('admin', 'admin/*') ? 'admin.login' : 'login');
        }

        if ($roles !== [] && ! in_array($user->role, $roles, true)) {
            if ($wantsJson) {
                return response()->json(['message' => 'Akses ditolak untuk peran ini.'], 403);
            }

            abort(403, 'Akses ditolak untuk peran ini.');
        }

        return $next($request);
    }
}
