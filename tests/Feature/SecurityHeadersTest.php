<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_sent_on_web_responses(): void
    {
        $this->withoutVite();

        $response = $this->get('/login');

        $response->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'geolocation=(), camera=(), microphone=()')
            ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
            ->assertHeader('Cross-Origin-Resource-Policy', 'same-origin')
            ->assertHeader('X-Permitted-Cross-Domain-Policies', 'none');

        $this->assertNotNull(
            $response->headers->get('Content-Security-Policy-Report-Only')
        );
    }

    public function test_security_headers_are_sent_on_error_responses(): void
    {
        $response = $this->get('/halaman-tidak-ada');

        $response->assertNotFound()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_sensitive_repository_paths_are_not_routable(): void
    {
        $paths = [
            '/.env',
            '/.git/config',
            '/composer.json',
            '/composer.lock',
            '/phpunit.xml',
            '/package.json',
            '/README.md',
            '/storage/logs/laravel.log',
            '/database/database.sqlite',
        ];

        foreach ($paths as $path) {
            $this->get($path)->assertNotFound();
        }
    }
}
