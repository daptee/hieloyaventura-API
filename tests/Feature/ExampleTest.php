<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        // El endpoint público de FAQs es accesible sin autenticación y no depende de APIs externas
        $response = $this->get('/api/faqs');

        $response->assertStatus(200);
    }
}
