<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Restablecimiento de contraseña - Hielo & Aventura</title>
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
        .wrapper { max-width: 600px; margin: 0 auto; }
        .email-header { text-align: center; padding: 0 0 24px 0; }
        .email-header img { height: 60px; width: auto; }
        .card {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 36px 40px;
        }
        .greeting { font-size: 18px; font-weight: 500; color: #333333; margin-bottom: 16px; }
        .intro-text { font-size: 14px; color: #555555; margin-bottom: 28px; line-height: 1.6; }
        .password-container { text-align: center; margin: 0 0 28px 0; }
        .password-label {
            font-size: 12px; font-weight: 700; color: #888888;
            text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 10px;
        }
        .password-box {
            display: inline-block;
            background-color: #f5f8fd;
            border: 2px solid #3686C3;
            border-radius: 10px;
            padding: 18px 40px;
            font-family: 'Courier New', monospace;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 4px;
            color: #3686C3;
            max-width: 100%;
            box-sizing: border-box;
            word-break: break-all;
        }
        @media only screen and (max-width: 480px) {
            .password-box {
                font-size: 18px;
                letter-spacing: 2px;
                padding: 14px 16px;
            }
        }
        .notice-box {
            background-color: #fff8e1;
            border-left: 4px solid #f59e0b;
            border-radius: 6px;
            padding: 14px 18px;
            font-size: 13px;
            color: #666666;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .steps {
            background-color: #f5f8fd;
            border-radius: 6px;
            padding: 16px 20px;
            margin-bottom: 24px;
        }
        .steps-title { font-size: 13px; font-weight: 700; color: #333; margin-bottom: 10px; }
        .steps ol { padding-left: 18px; }
        .steps li { font-size: 13px; color: #555555; line-height: 1.8; }
        .divider { border: none; border-top: 1px solid #e0e0e0; margin: 24px 0; }
        .contact-text { font-size: 14px; color: #555555; margin-bottom: 10px; }
        .contact-detail { font-size: 14px; color: #333333; margin-bottom: 4px; }
        .contact-detail strong { font-weight: 700; }
        .thanks-section {
            border-top: 1px solid #e0e0e0;
            margin-top: 24px; padding-top: 24px; text-align: center;
        }
        .thanks-text { font-size: 15px; font-weight: 500; color: #3686C3; }
        .email-footer { text-align: center; padding: 28px 0 10px 0; }
        .social-links { margin-bottom: 14px; }
        .social-links a {
            display: inline-block; margin: 0 8px;
            color: #555555; text-decoration: none; font-size: 13px;
        }
        .footer-text { font-size: 12px; color: #999999; margin-bottom: 6px; line-height: 1.5; }
        .footer-link { color: #3686C3; text-decoration: underline; }
        .footer-site { font-size: 13px; font-weight: 700; color: #3686C3; text-decoration: none; display: block; margin-bottom: 6px; }
        .footer-dev { font-size: 11px; color: #bbbbbb; }
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

            <p class="greeting">Hola, {{ $user_name }}.</p>

            <p class="intro-text">
                Por motivos de seguridad, el equipo de <strong>Hielo &amp; Aventura</strong> ha restablecido
                la contraseña de tu cuenta de acceso al sistema de agencias.<br><br>
                A continuación encontrás tu nueva contraseña temporal. Por favor, ingresá con ella
                y cambiala lo antes posible desde tu perfil.
            </p>

            {{-- PASSWORD --}}
            <div class="password-container">
                <p class="password-label">Tu nueva contraseña temporal</p>
                <div class="password-box">{{ $new_password }}</div>
            </div>

            {{-- WARNING --}}
            <div class="notice-box">
                <strong>⚠ Importante:</strong> Esta contraseña es temporal. Te recomendamos cambiarla
                inmediatamente después de ingresar al sistema desde <em>Mi Perfil → Cambiar contraseña</em>.
            </div>

            {{-- STEPS --}}
            <div class="steps">
                <p class="steps-title">Pasos para ingresar:</p>
                <ol>
                    <li>Accedé a <strong>agencias.hieloyaventura.com</strong></li>
                    <li>Ingresá tu email y la contraseña temporal indicada arriba</li>
                    <li>Verificá tu identidad con el código que recibirás por email (2FA)</li>
                    <li>Desde tu perfil, cambiá tu contraseña por una propia</li>
                </ol>
            </div>

            {{-- CONTACT --}}
            <hr class="divider">
            <p class="contact-text">Ante cualquier consulta, no dudes en contactarnos.</p>
            <p class="contact-detail"><strong>Contacto:</strong> reservas@hieloyaventura.com</p>
            <p class="contact-detail"><strong>Teléfono:</strong> +54-2902-492205 o 2902-490205 de 7 a 20:00hs.</p>

            {{-- THANKS --}}
            <div class="thanks-section">
                <p class="thanks-text">¡Muchas gracias por elegir Hielo y Aventura!</p>
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
