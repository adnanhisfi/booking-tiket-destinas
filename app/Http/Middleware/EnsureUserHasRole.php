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

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($roles !== [] && ! in_array($user->role, $roles, true)) {
            return response()->json(['message' => 'Akses ditolak untuk peran ini.'], 403);
        }

        return $next($request);
    }
}
