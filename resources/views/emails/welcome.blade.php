<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Bienvenido a Hielo & Aventura</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

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
            padding: 30px 15px;
        }

        .email-header {
            text-align: center;
            padding-bottom: 22px;
        }

        .email-header img {
            height: 60px;
            width: auto;
        }

        .card {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 34px 34px 28px 34px;
            box-shadow: 0 6px 20px rgba(20, 33, 61, 0.08);
        }

        .eyebrow {
            display: inline-block;
            background-color: #eaf4fc;
            color: #2c6fa3;
            border-radius: 20px;
            padding: 5px 12px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.4px;
            margin-bottom: 14px;
        }

        .title {
            font-size: 24px;
            line-height: 1.2;
            color: #1f2b37;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .subtitle {
            font-size: 14px;
            line-height: 1.6;
            color: #55616f;
            margin-bottom: 20px;
        }

        .content-box {
            background-color: #f9fbfd;
            border: 1px solid #e1e8ef;
            border-radius: 8px;
            padding: 18px;
            font-size: 14px;
            line-height: 1.65;
            color: #2f3a46;
        }

        .content-box strong {
            color: #1f2b37;
        }

        .security-note {
            margin-top: 18px;
            font-size: 12px;
            color: #6a7480;
            line-height: 1.5;
        }

        .cta {
            margin-top: 24px;
            text-align: center;
        }

        .cta a {
            display: inline-block;
            background-color: #e8b455;
            color: #ffffff !important;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.3px;
            padding: 12px 32px;
            border-radius: 24px;
        }

        .footer {
            text-align: center;
            padding: 22px 0 8px 0;
        }

        .footer-site {
            display: inline-block;
            color: #3686c3;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .footer-copy {
            font-size: 11px;
            color: #9aa3ad;
        }
    </style>
</head>

<body style="background-color: #f0f2f5; margin: 0; padding: 0;">
    <div class="wrapper">
        <div class="email-header">
            <img src="{{ config('app.url') }}/images/logo.png" alt="Hielo & Aventura" onerror="this.style.display='none'">
        </div>

        <div class="card">
            <span class="eyebrow">NUEVA CUENTA</span>
            <h1 class="title">Bienvenido a Hielo & Aventura</h1>
            <p class="subtitle">
                Tu compra se registro correctamente y creamos una cuenta para que puedas gestionar tus reservas de forma simple y segura.
            </p>

            <div class="content-box">
                {!! $msg !!}
            </div>

            <p class="security-note">
                Por seguridad, te recomendamos cambiar tu contrasena luego del primer acceso.
            </p>

            <div class="cta">
                <a href="https://hieloyaventura.com">Ir al sitio</a>
            </div>
        </div>

        <div class="footer">
            <a class="footer-site" href="https://hieloyaventura.com">hieloyaventura.com</a>
            <p class="footer-copy">Este es un correo automatico. Por favor no responder.</p>
        </div>
    </div>
</body>

</html>