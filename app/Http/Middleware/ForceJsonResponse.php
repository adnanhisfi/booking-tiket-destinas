<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Paksa request API dianggap sebagai permintaan JSON.
     *
     * Tanpa ini, request tanpa header "Accept: application/json" akan
     * diarahkan ke route "login" yang tidak ada sehingga menghasilkan 500.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
