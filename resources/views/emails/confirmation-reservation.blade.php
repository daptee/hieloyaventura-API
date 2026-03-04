<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Confirmación de Reserva - Hielo & Aventura</title>
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
        /* DATA SECTION */
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
        }
        /* NOTICE BOX */
        .notice-box {
            background-color: #f5f5f5;
            border-radius: 6px;
            padding: 14px 18px;
            margin: 24px 0;
            font-size: 13px;
            color: #666666;
            font-weight: 400;
            line-height: 1.5;
        }
        /* CONTACT */
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
        .rates-notice {
            font-size: 13px;
            color: #888888;
            margin-top: 12px;
        }
        /* THANKS & BUTTON */
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
            <p class="agency-name">{{ $agency_name ?? 'Agencia' }}</p>

            <p class="intro-bold">Este es un correo automático con la confirmación de tu reserva.</p>
            <p class="intro-text">
                Se ha recibido una notificación mediante integración de API de la agencia
                <strong>{{ $agency_name ?? 'la agencia' }}</strong> con la confirmación de los siguientes datos:
            </p>

            {{-- DATA TABLE --}}
            <p class="section-title">Datos de la reserva</p>
            <hr class="divider">
            <table class="data-table">
                <tr>
                    <td class="label">Agencia:</td>
                    <td class="value">{{ $agency_name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Excursión:</td>
                    <td class="value">{{ $data->excurtion->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Nro reserva:</td>
                    <td class="value">{{ $data->reservation_number ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Fecha y hora:</td>
                    <td class="value">
                        {{ isset($data->date) ? $data->date->format('d/m/Y') : '-' }}
                        @if($turn) – {{ $turn }}@endif
                    </td>
                </tr>
                <tr>
                    <td class="label">Traslado:</td>
                    <td class="value">{{ $data->is_transfer ? 'Sí' : 'No' }}</td>
                </tr>
                <tr>
                    <td class="label">Nombre:</td>
                    <td class="value">{{ $reservation_name ?? $request->contact_name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Pasajeros:</td>
                    <td class="value">{{ $number_of_passengers ?? $request->paxs_cant ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Hotel:</td>
                    <td class="value">{{ $data->is_transfer ? ($data->hotel_name ?? '-') : '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Punto de encuentro:</td>
                    <td class="value">{{ $data->meeting_point ?? '-' }}</td>
                </tr>
            </table>

            {{-- NOTICE --}}
            <div class="notice-box">
                Tarifa vigente al momento de confirmar la reserva. Puede sufrir cambios sin previo aviso.
            </div>

            {{-- CONTACT --}}
            <div class="contact-section">
                <p class="contact-text">Cualquier duda puede contactarse con nosotros.</p>
                <p class="contact-detail"><strong>Contacto:</strong> reservas@hieloyaventura.com</p>
                <p class="contact-detail"><strong>Teléfono:</strong> +54-2902-492205 o 2902-490205 de 7 a 20:00hs.</p>
                <p class="rates-notice">Las tarifas están sujetas a cambios SIN previo aviso.</p>
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
            <p class="footer-text">
                El uso de nuestro servicio y sitio web está sujeto a nuestros<br>
                <a href="https://agencias.hieloyaventura.com/agencias-terminos-y-condiciones.pdf" class="footer-link">Términos de uso y Política de privacidad.</a>
            </p>
            <a href="https://www.hieloyaventura.com.ar" class="footer-site">www.hieloyaventura.com.ar</a>
            <p class="footer-dev">Desarrollado por <strong>Daptee</strong></p>
        </div>

    </div>
</body>
</html>
