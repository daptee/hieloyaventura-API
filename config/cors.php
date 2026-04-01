<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Configurar CORS_ALLOWED_ORIGINS en .env con los dominios permitidos,
    | separados por coma. Ejemplo:
    |   CORS_ALLOWED_ORIGINS=https://hieloyaventura.com,https://admin.hieloyaventura.com,https://agencias.hieloyaventura.com
    |
    | En desarrollo se puede usar * para permitir cualquier origen.
    | En producción SIEMPRE listar los dominios explícitamente.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => (function () {
        $origins = env('CORS_ALLOWED_ORIGINS');
        $appEnv = env('APP_ENV', 'production');

        // OBLIGATORIO en producción y staging
        if (in_array($appEnv, ['production', 'staging'])) {
            if (empty($origins)) {
                throw new \Exception(
                    'CORS_ALLOWED_ORIGINS está vacío o no configurado. '
                        . 'En ' . $appEnv . ' DEBE estar explícitamente en .env. '
                        . 'Ejemplo: CORS_ALLOWED_ORIGINS=https://hieloyaventura.com,https://admin.hieloyaventura.com'
                );
            }
        }

        // En desarrollo, si está vacío, permitir *
        if (empty($origins)) {
            return ['*'];
        }

        return array_map('trim', explode(',', $origins));
    })(),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
