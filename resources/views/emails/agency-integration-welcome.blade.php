<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Bienvenido a la integración por API - Hielo & Aventura</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: #f0f2f5;
            font-family: 'Nunito', Arial, sans-serif;
            color: #333333;
            margin: 0;
            padding: 0;
        }
        .wrapper {
            max-width: 600px;
            margin: 0 auto;
        }
        .email-header {
            text-align: center;
            padding: 0 0 24px 0;
        }
        .email-header img {
            height: 60px;
            width: auto;
        }
        .card {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 36px 40px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 500;
            color: #333333;
            margin-bottom: 6px;
        }
        .agency-name {
            font-size: 20px;
            font-weight: 700;
            color: #3686C3;
            margin-bottom: 18px;
        }
        .intro-bold {
            font-size: 15px;
            font-weight: 700;
            color: #333333;
            margin-bottom: 10px;
        }
        .intro-text {
            font-size: 14px;
            font-weight: 400;
            color: #555555;
            margin-bottom: 24px;
            line-height: 1.6;
        }
        .section-title {
            font-size: 15px;
            font-weight: 700;
            color: #333333;
            margin-bottom: 8px;
        }
        .divider {
            border: none;
            border-top: 1px solid #e0e0e0;
            margin-bottom: 0;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table tr {
            border-bottom: 1px solid #eeeeee;
        }
        .data-table td {
            padding: 10px 4px;
            font-size: 14px;
            line-height: 1.5;
        }
        .data-table td.label {
            font-weight: 700;
            width: 40%;
            color: #333333;
        }
        .data-table td.value {
            font-weight: 400;
            color: #555555;
            word-break: break-all;
        }
        .env-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
        }
        .env-production {
            background-color: #e6f4ea;
            color: #2d7a3a;
        }
        .env-development {
            background-color: #fff3e0;
            color: #b45309;
        }
        .apikey-box {
            background-color: #f5f5f5;
            border-radius: 6px;
            padding: 14px 18px;
            margin: 20px 0;
            font-size: 13px;
            color: #333333;
            line-height: 1.6;
            word-break: break-all;
        }
        .apikey-box strong {
            display: block;
            font-size: 12px;
            color: #888888;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .apikey-value {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #3686C3;
            font-weight: 700;
        }
        .notice-box {
            background-color: #f5f5f5;
            border-radius: 6px;
            padding: 14px 18px;
            margin: 24px 0;
            font-size: 13px;
            color: #666666;
            line-height: 1.6;
        }
        .contact-section {
            border-top: 1px solid #e0e0e0;
            padding-top: 20px;
            margin-top: 20px;
        }
        .contact-text {
            font-size: 14px;
            color: #555555;
            margin-bottom: 10px;
        }
        .contact-detail {
            font-size: 14px;
            color: #333333;
            margin-bottom: 4px;
        }
        .contact-detail strong {
            font-weight: 700;
        }
        .thanks-section {
            border-top: 1px solid #e0e0e0;
            margin-top: 24px;
            padding-top: 24px;
            text-align: center;
        }
        .thanks-text {
            font-size: 15px;
            font-weight: 500;
            color: #3686C3;
            margin-bottom: 20px;
        }
        .btn-web {
            display: inline-block;
            background-color: #E8B455;
            color: #ffffff !important;
            text-decoration: none;
            font-family: 'Nunito', Arial, sans-serif;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 13px 60px;
            border-radius: 30px;
        }
        .email-footer {
            text-align: center;
            padding: 28px 0 10px 0;
        }
        .social-links {
            margin-bottom: 14px;
        }
        .social-links a {
            display: inline-block;
            margin: 0 8px;
            color: #555555;
            text-decoration: none;
            font-size: 13px;
        }
        .footer-text {
            font-size: 12px;
            color: #999999;
            margin-bottom: 6px;
            line-height: 1.5;
        }
        .footer-link {
            color: #3686C3;
            text-decoration: underline;
        }
        .footer-site {
            font-size: 13px;
            font-weight: 700;
            color: #3686C3;
            text-decoration: none;
            display: block;
            margin-bottom: 6px;
        }
        .footer-dev {
            font-size: 11px;
            color: #bbbbbb;
        }
    </style>
</head>
<body style="background-color: #f0f2f5; margin: 0; padding: 0;">
    <div class="wrapper" style="background-color: #f0f2f5; padding: 30px 15px;">

        {{-- LOGO --}}
        <div class="email-header">
            <img src="{{ config('app.url') }}/images/logo.png"
                 alt="Hielo & Aventura"
                 onerror="this.style.display='none'">
        </div>

        {{-- CARD --}}
        <div class="card">

            <p class="greeting">Hola,</p>
            <p class="agency-name">{{ $agencyName }}</p>

            <p class="intro-bold">¡Bienvenido a la integración por API de Hielo & Aventura!</p>
            <p class="intro-text">
                Tu agencia ha sido activada para utilizar nuestra integración por API. A continuación encontrarás
                los datos necesarios para comenzar a operar.
            </p>

            {{-- DATA TABLE --}}
            <p class="section-title">Datos de acceso</p>
            <hr class="divider">
            <table class="data-table">
                <tr>
                    <td class="label">Agencia:</td>
                    <td class="value">{{ $agencyName }}</td>
                </tr>
                <tr>
                    <td class="label">Ambiente:</td>
                    <td class="value">
                        @if($environment === 'production')
                            <span class="env-badge env-production">Producción</span>
                        @else
                            <span class="env-badge env-development">Desarrollo</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label">Documentación:</td>
                    <td class="value">
                        <a href="{{ $documentationUrl }}" style="color: #3686C3; text-decoration: underline;">
                            Ver documentación en Postman
                        </a>
                    </td>
                </tr>
            </table>

            {{-- API KEY BOX --}}
            <div class="apikey-box">
                <strong>Tu API Key</strong>
                <span class="apikey-value">{{ $apiKey }}</span>
            </div>

            {{-- NOTICE --}}
            <div class="notice-box">
                Guardá tu API Key en un lugar seguro. Esta clave es personal e intransferible y permite
                identificar a tu agencia en cada solicitud.
                @if($environment !== 'production')
                    <br><br>
                    <strong>Nota:</strong> Estás dado de alta en el ambiente de <strong>Desarrollo</strong>.
                    El link de documentación es el mismo para ambos ambientes, pero el API Key cambia entre
                    Desarrollo y Producción.
                @endif
            </div>

            {{-- CONTACT --}}
            <div class="contact-section">
                <p class="contact-text">Ante cualquier consulta sobre la integración, no dudes en contactarnos.</p>
                <p class="contact-detail"><strong>Contacto:</strong> reservas@hieloyaventura.com</p>
                <p class="contact-detail"><strong>Teléfono:</strong> +54-2902-492205 o 2902-490205 de 7 a 20:00hs.</p>
            </div>

            {{-- THANKS --}}
            <div class="thanks-section">
                <p class="thanks-text">¡Muchas gracias por elegir Hielo y Aventura!</p>
                <a href="{{ $documentationUrl }}" class="btn-web">VER DOCUMENTACIÓN</a>
            </div>

        </div>

        {{-- FOOTER --}}
        <div class="email-footer">
            <div class="social-links">
                <a href="https://www.instagram.com/hieloyaventura" target="_blank">Instagram</a>
                <a href="https://www.facebook.com/hieloyaventura" target="_blank">Facebook</a>
                <a href="https://www.youtube.com/hieloyaventura" target="_blank">YouTube</a>
            </div>
            <a href="https://www.hieloyaventura.com" class="footer-site">www.hieloyaventura.com</a>
            <p class="footer-dev">Desarrollado por <strong>Daptee</strong></p>
        </div>

    </div>
</body>
</html>
