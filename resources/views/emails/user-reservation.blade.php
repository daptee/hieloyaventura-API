<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Confirmación de Reserva - Hielo & Aventura</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;700&display=swap" rel="stylesheet">
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

        .client-name {
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
            width: 42%;
            color: #333333;
        }

        .data-table td.value {
            font-weight: 400;
            color: #555555;
        }

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

        .bigice-box {
            background-color: #fff8ec;
            border: 1px solid #E8B455;
            border-radius: 6px;
            padding: 14px 18px;
            margin: 0 0 24px 0;
            font-size: 13px;
            color: #7a5520;
            line-height: 1.6;
        }

        .bigice-box a {
            color: #3686C3;
            word-break: break-all;
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

        .rates-notice {
            font-size: 13px;
            color: #888888;
            margin-top: 12px;
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

            <p class="greeting">¡Hola! 👋</p>
            @if($userReservation && $userReservation->contact_data)
            <p class="client-name">{{ $userReservation->contact_data->name }}</p>
            @endif

            <p class="intro-bold">¡Tu reserva ha sido confirmada con éxito!</p>
            <p class="intro-text">{!! $msg !!}</p>

            {{-- RESERVATION DATA TABLE --}}
            <p class="section-title">Datos de tu reserva</p>
            <hr class="divider">

            <table class="data-table">
                @if($userReservation)
                <tr>
                    <td class="label">Nro. de reserva:</td>
                    <td class="value">{{ $userReservation->reservation_number ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Excursión:</td>
                    <td class="value">{{ $userReservation->excurtion->name ?? $excurtion_name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Fecha y hora:</td>
                    <td class="value">
                        {{ isset($userReservation->date) ? $userReservation->date->format('d/m/Y') : '-' }}
                        @if($userReservation->turn) – {{ $userReservation->turn->format('H:i') }}hs @endif
                    </td>
                </tr>
                <tr>
                    <td class="label">Traslado:</td>
                    <td class="value">{{ $userReservation->is_transfer ? 'Sí' : 'No' }}</td>
                </tr>
                <tr>
                    <td class="label">Punto de encuentro:</td>
                    <td class="value">{{ $meeting_point ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Pasajeros:</td>
                    <td class="value">{{ $userReservation->paxes->count() ?: '-' }}</td>
                </tr>
                @endif
                @if($payment_method)
                @php
                $paymentLabels = [
                'mercadopago' => 'MercadoPago',
                'credit_card' => 'Tarjeta de Crédito',
                'debit_card' => 'Tarjeta de Débito',
                'card' => 'Tarjeta de Débito/Crédito',
                'paypal' => 'PayPal',
                ];
                $paymentDisplay = $paymentLabels[strtolower($payment_method)] ?? $payment_method;
                $isCard = in_array(strtolower($payment_method), ['credit_card', 'debit_card', 'card']);
                @endphp
                <tr>
                    <td class="label">Método de pago:</td>
                    <td class="value">{{ $paymentDisplay }}</td>
                </tr>
                @if($installments && $isCard)
                <tr>
                    <td class="label">Cuotas:</td>
                    <td class="value">{{ $installments }} {{ $installments == 1 ? 'cuota' : 'cuotas' }}
                        @if($installment_surcharge)
                        <span style="color:#c0392b; font-size:13px;">(+{{ $installment_surcharge }}% de recargo)</span>
                        @endif
                    </td>
                </tr>
                @endif
                @endif
            </table>

            {{-- NOTICE --}}
            <div class="notice-box">
                Tarifa vigente al momento de confirmar la reserva. Puede sufrir cambios sin previo aviso.
            </div>

            {{-- MEDICAL RECORD (Big Ice) --}}
            @if($bigice)
            <div class="bigice-box">
                {{ $msg_is_bigice }}
                <br><br>
                <a href="{{ config('app.url_hya') }}/ficha-medica/{{ $hash_reservation_number }}">
                    {{ config('app.url_hya') }}/ficha-medica/{{ $hash_reservation_number }}
                </a>
            </div>
            @endif

            {{-- CONTACT --}}
            <div class="contact-section">
                <p class="contact-text">Ante cualquier consulta, no dudes en contactarnos.</p>
                <p class="contact-detail"><strong>Email:</strong> info@hieloyaventura.com</p>
                <p class="contact-detail"><strong>Teléfono:</strong> +54-2902-492205 o 2902-490205 de 7 a 20:00hs.</p>
                <p class="rates-notice">Las tarifas están sujetas a cambios SIN previo aviso.</p>
            </div>

            {{-- THANKS & BUTTON --}}
            <div class="thanks-section">
                <p class="thanks-text">¡Muchas gracias por elegir Hielo y Aventura!</p>
                <a href="{{ config('app.url_hya', 'https://www.hieloyaventura.com') }}" class="btn-web">IR A LA WEB</a>
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