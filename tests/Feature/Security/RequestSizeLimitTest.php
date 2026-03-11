<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifica que el middleware RequestSizeLimit rechaza con 413 las requests
 * que superan el límite configurado:
 *   - JSON / API estándar: 1 MB
 *   - Uploads (multipart/form-data): 10 MB
 */
class RequestSizeLimitTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Requests dentro del límite — deben pasar normalmente
    // -------------------------------------------------------------------------

    public function test_request_pequeño_pasa_sin_problema(): void
    {
        // Una request normal de login es de unos pocos bytes, nunca debería ser rechazada
        $response = $this->postJson('/api/login', [
            'email'    => 'test@test.com',
            'password' => 'password',
        ]);

        // No importa el resultado del login, solo que no sea 413
        $this->assertNotEquals(413, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Requests que superan el límite de 1 MB para JSON
    // -------------------------------------------------------------------------

    public function test_request_json_mayor_a_1mb_retorna_413(): void
    {
        // Enviar un payload JSON real de más de 1 MB
        // El test client de Laravel setea Content-Length al tamaño real del body
        $largeString = str_repeat('x', (1 * 1024 * 1024) + 100);

        $response = $this->postJson('/api/login', [
            'email'    => 'test@test.com',
            'password' => 'password',
            'pad'      => $largeString,
        ]);

        $response->assertStatus(413)
                 ->assertJsonFragment(['message' => 'El tamaño del request supera el límite permitido.']);
    }

    public function test_request_upload_mayor_a_10mb_retorna_413(): void
    {
        // Para multipart, el límite es 10 MB. Enviamos un body real de 10 MB+.
        // En tests simulamos con Content-Length en el header del request ya que
        // enviar 10 MB reales sería muy lento. Usamos el helper de Symfony directamente.
        $oversizeBytes = (10 * 1024 * 1024) + 1;

        $response = $this->call('POST', '/api/login', [], [], [], [
            'CONTENT_TYPE'   => 'multipart/form-data; boundary=test',
            'CONTENT_LENGTH' => (string) $oversizeBytes,
        ]);

        $response->assertStatus(413);
    }
}
