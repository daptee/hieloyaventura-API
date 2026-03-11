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

    'allowed_origins' => array_map(
        'trim',
        explode(',', env('CORS_ALLOWED_ORIGINS', '*'))
    ),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
