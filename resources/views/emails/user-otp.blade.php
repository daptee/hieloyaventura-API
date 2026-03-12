<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Código de verificación - Hielo & Aventura</title>
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
            margin-bottom: 16px;
        }
        .intro-text {
            font-size: 14px;
            font-weight: 400;
            color: #555555;
            margin-bottom: 28px;
            line-height: 1.6;
        }
        .otp-container {
            text-align: center;
            margin: 0 0 28px 0;
        }
        .otp-label {
            font-size: 12px;
            font-weight: 700;
            color: #888888;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 10px;
        }
        .otp-code {
            display: inline-block;
            background-color: #f5f8fd;
            border: 2px solid #3686C3;
            border-radius: 10px;
            padding: 18px 40px;
            font-family: 'Courier New', monospace;
            font-size: 42px;
            font-weight: 700;
            letter-spacing: 14px;
            color: #3686C3;
        }
        .expiry-text {
            text-align: center;
            font-size: 13px;
            color: #888888;
            margin-bottom: 28px;
        }
        .notice-box {
            background-color: #f5f5f5;
            border-radius: 6px;
            padding: 14px 18px;
            font-size: 13px;
            color: #666666;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .divider {
            border: none;
            border-top: 1px solid #e0e0e0;
            margin: 24px 0;
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

            @if($type === 'email_change')
                <p class="intro-text">
                    Recibimos una solicitud para <strong>cambiar el correo electrónico</strong> asociado a tu cuenta
                    en Hielo &amp; Aventura.<br><br>
                    Para confirmar el cambio, ingresá el código de verificación que figura a continuación.
                    Si no realizaste esta solicitud, podés ignorar este mensaje y tu correo no será modificado.
                </p>
            @elseif($type === 'password_change')
                <p class="intro-text">
                    Recibimos una solicitud para <strong>cambiar la contraseña</strong> de tu cuenta
                    en Hielo &amp; Aventura.<br><br>
                    Para confirmar el cambio, ingresá el código de verificación que figura a continuación.
                    Si no realizaste esta solicitud, contactanos de inmediato.
                </p>
            @else
                <p class="intro-text">
                    Estás iniciando sesión en Hielo &amp; Aventura.
                    Ingresá el siguiente código para completar el acceso.
                </p>
            @endif

            {{-- OTP CODE --}}
            <div class="otp-container">
                <p class="otp-label">Tu código de verificación</p>
                <div class="otp-code">{{ $otp_code }}</div>
            </div>

            <p class="expiry-text">Este código expira en <strong>10 minutos</strong>.</p>

            {{-- NOTICE --}}
            <div class="notice-box">
                @if($type === 'email_change')
                    Si no solicitaste este cambio, no es necesario que hagas nada. Tu correo actual seguirá siendo el mismo.
                @elseif($type === 'password_change')
                    Si no solicitaste este cambio, contactanos de inmediato a través de los datos que figuran más abajo.
                @else
                    Si no intentaste iniciar sesión, alguien podría estar intentando acceder a tu cuenta.
                    Te recomendamos cambiar tu contraseña por seguridad.
                @endif
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
            <a href="https://www.hieloyaventura.com.ar" class="footer-site">www.hieloyaventura.com.ar</a>
            <p class="footer-dev">Desarrollado por <strong>Daptee</strong></p>
        </div>

    </div>
</body>
</html>
