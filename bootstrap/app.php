<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);

        $middleware->web(append: [
            SecurityHeaders::class,
        ]);

        $middleware->api(append: [
            SecurityHeaders::class,
        ]);

        $middleware->throttleApi('api');

        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('admin', 'admin/*')
            ? route('admin.login')
            : route('login'));

        $middleware->redirectUsersTo(fn (Request $request) => $request->user()?->isAdmin()
            ? route('admin.dashboard')
            : route('katalog.beranda'));

        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*') || $request->expectsJson());

        // Respons error dibuat di luar alur middleware, jadi header keamanan
        // perlu ditempelkan ulang pada tahap finalisasi respons exception.
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            return SecurityHeaders::apply($response, $request);
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            [$status, $message] = match (true) {
                $e instanceof ValidationException => [422, 'Data yang dikirim tidak valid.'],
                $e instanceof AuthenticationException => [401, 'Sesi tidak valid. Silakan login kembali.'],
                $e instanceof AuthorizationException,
                $e instanceof AccessDeniedHttpException => [403, 'Akses ditolak.'],
                $e instanceof ModelNotFoundException,
                $e instanceof NotFoundHttpException => [404, 'Data atau endpoint tidak ditemukan.'],
                $e instanceof MethodNotAllowedHttpException => [405, 'Metode HTTP tidak diizinkan.'],
                $e instanceof ThrottleRequestsException => [429, 'Terlalu banyak permintaan. Silakan coba lagi nanti.'],
                $e instanceof HttpExceptionInterface => [
                    $e->getStatusCode(),
                    $e->getMessage() !== '' ? $e->getMessage() : 'Permintaan tidak dapat diproses.',
                ],
                default => [500, 'Terjadi kesalahan pada server.'],
            };

            $payload = [
                'success' => false,
                'message' => $message,
                'code' => $status,
            ];

            if ($e instanceof ValidationException) {
                $payload['errors'] = $e->errors();
            }

            if ($status >= 500) {
                Log::error('API error', [
                    'exception' => $e,
                    'method' => $request->method(),
                    'path' => $request->path(),
                ]);
            }

            $headers = $e instanceof ThrottleRequestsException ? $e->getHeaders() : [];

            return response()->json($payload, $status, $headers);
        });
    })->create();
