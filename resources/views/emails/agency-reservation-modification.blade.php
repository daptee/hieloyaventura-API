<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Pedido de Modificación de Reserva - Hielo & Aventura</title>
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
        /* HEADER - Logo */
        .email-header {
            text-align: center;
            padding: 0 0 24px 0;
        }
        .email-header img {
            height: 60px;
            width: auto;
        }
        /* CARD */
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
        .recipient-name {
            font-size: 20px;
            font-weight: 700;
            color: #3686C3;
            margin-bottom: 18px;
        }
        .subject-title {
            font-size: 16px;
            font-weight: 700;
            color: #333333;
            margin-bottom: 14px;
        }
        .intro-text {
            font-size: 14px;
            font-weight: 400;
            color: #555555;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        /* DATA */
        .data-line {
            font-size: 14px;
            color: #333333;
            margin-bottom: 8px;
            line-height: 1.5;
        }
        .data-line strong {
            font-weight: 700;
        }
        .urgency-text {
            font-size: 14px;
            color: #555555;
            margin-top: 20px;
            line-height: 1.5;
        }
        /* NOTICE BOX */
        .notice-box {
            background-color: #f5f5f5;
            border-radius: 6px;
            padding: 14px 18px;
            margin: 24px 0;
            font-size: 13px;
            color: #666666;
            line-height: 1.6;
        }
        .notice-box a {
            color: #3686C3;
            text-decoration: none;
            font-weight: 700;
        }
        /* THANKS & BUTTON */
        .thanks-section {
            text-align: center;
            margin-top: 24px;
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
        /* FOOTER */
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

            <p class="greeting">Hola, 👋</p>
            <p class="recipient-name">Equipo de Reservas</p>

            <p class="subject-title">Pedido de Modificación de Reserva</p>

            <p class="intro-text">
                Se ha recibido una notificación mediante integración de API de la agencia
                <strong>{{ $agencyName }}</strong> pidiendo modificación de los siguientes datos:
            </p>

            {{-- REQUEST DATA --}}
            @foreach($formattedData as $field)
                <p class="data-line">
                    <strong>{{ $field['label'] }}:</strong> {{ $field['value'] }}
                </p>
            @endforeach

            <p class="data-line" style="margin-top: 12px;">
                <strong>Número de Reserva:</strong> {{ $reservationNumber }}
            </p>

            <p class="urgency-text">Por favor, procese esta solicitud a la brevedad posible.</p>

            {{-- NOTICE --}}
            <div class="notice-box">
                Este es un correo automático generado por el sistema de integración de API.<br>
                <a href="{{ config('app.url_agencies', 'https://agencias.hieloyaventura.com') }}">Hielo y Aventura</a> - Sistema de Gestión de Reservas
            </div>

            {{-- THANKS & BUTTON --}}
            <div class="thanks-section">
                <p class="thanks-text">¡Muchas gracias por elegir Hielo y Aventura!</p>
                <a href="{{ config('app.url_agencies', 'https://agencias.hieloyaventura.com') }}" class="btn-web">IR A LA WEB</a>
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
