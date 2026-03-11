<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifica que el middleware SecurityHeaders agrega los headers HTTP de seguridad
 * en todas las respuestas de la API, tanto en endpoints públicos como protegidos.
 *
 * Headers requeridos:
 *   - X-Content-Type-Options: nosniff
 *   - X-Frame-Options: DENY
 *   - X-XSS-Protection: 1; mode=block
 *   - Strict-Transport-Security: max-age=31536000; includeSubDomains
 *   - Referrer-Policy: strict-origin-when-cross-origin
 *   - Permissions-Policy: geolocation=(), microphone=(), camera=()
 */
class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    private array $requiredHeaders = [
        'X-Content-Type-Options'    => 'nosniff',
        'X-Frame-Options'           => 'DENY',
        'X-XSS-Protection'          => '1; mode=block',
        'Referrer-Policy'           => 'strict-origin-when-cross-origin',
    ];

    // -------------------------------------------------------------------------
    // Headers presentes en endpoints públicos
    // -------------------------------------------------------------------------

    public function test_endpoint_publico_incluye_security_headers(): void
    {
        $response = $this->getJson('/api/faqs');

        foreach ($this->requiredHeaders as $header => $value) {
            $response->assertHeader($header, $value);
        }
    }

    public function test_endpoint_de_login_incluye_security_headers(): void
    {
        $response = $this->postJson('/api/login', [
            'email'    => 'noexiste@test.com',
            'password' => 'password',
        ]);

        foreach ($this->requiredHeaders as $header => $value) {
            $response->assertHeader($header, $value);
        }
    }

    // -------------------------------------------------------------------------
    // Headers presentes en respuestas de error (401, 403)
    // -------------------------------------------------------------------------

    public function test_respuesta_401_incluye_security_headers(): void
    {
        $response = $this->getJson('/api/agencies/CUALQUIERA');

        $response->assertStatus(401);

        foreach ($this->requiredHeaders as $header => $value) {
            $response->assertHeader($header, $value);
        }
    }

    public function test_permissions_policy_header_presente(): void
    {
        $response = $this->getJson('/api/faqs');

        $response->assertHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
    }

    public function test_strict_transport_security_presente(): void
    {
        $response = $this->getJson('/api/faqs');

        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }
}
