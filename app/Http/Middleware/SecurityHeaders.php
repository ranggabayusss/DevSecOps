<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'geolocation=(), camera=(), microphone=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'Cross-Origin-Resource-Policy' => 'same-origin',
            'X-Permitted-Cross-Domain-Policies' => 'none',
        ];

        foreach ($headers as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        $scriptSources = ["'self'"];
        $connectSources = ["'self'"];

        if (app()->environment('local') && file_exists(public_path('hot'))) {
            $scriptSources[] = 'http://localhost:5173';
            $scriptSources[] = 'http://127.0.0.1:5173';
            $connectSources[] = 'ws://localhost:5173';
            $connectSources[] = 'ws://127.0.0.1:5173';
        }

        $contentSecurityPolicy = implode('; ', [
            "default-src 'self'",
            'script-src '.implode(' ', $scriptSources),
            "style-src 'self'",
            "img-src 'self' data:",
            "font-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            'connect-src '.implode(' ', $connectSources),
        ]);

        if (
            ! $response->headers->has('Content-Security-Policy')
            && ! $response->headers->has('Content-Security-Policy-Report-Only')
        ) {
            $response->headers->set('Content-Security-Policy', $contentSecurityPolicy);
        }

        if ($request->isSecure() && ! $response->headers->has('Strict-Transport-Security')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        if ($request->user()) {
            $response->headers->set('Cache-Control', 'no-store, private');
        }

        $response->headers->remove('X-Powered-By');

        return $response;
    }
}
