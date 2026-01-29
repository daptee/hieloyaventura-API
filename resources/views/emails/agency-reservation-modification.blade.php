<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Modificación de Reserva</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #0066cc;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }

        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 0 0 5px 5px;
        }

        .data-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .data-table tr {
            border-bottom: 1px solid #ddd;
        }

        .data-table td {
            padding: 10px;
        }

        .data-table td:first-child {
            font-weight: bold;
            width: 40%;
            color: #0066cc;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Pedido de Modificación de Reserva</h2>
    </div>

    <div class="content">
        <p>Se ha recibido una notificación mediante integración de API de la agencia <strong>{{ $agencyName }}</strong>
            pidiendo modificación de los siguientes datos:</p>

        <table class="data-table">
            @foreach($formattedData as $field)
                <tr>
                    <td>{{ $field['label'] }}:</td>
                    <td>{{ $field['value'] }}</td>
                </tr>
            @endforeach
        </table>

        <p style="margin-top: 30px;">
            <strong>Número de Reserva:</strong> {{ $reservationNumber }}
        </p>

        <p style="margin-top: 20px; color: #666; font-size: 14px;">
            Por favor, procese esta solicitud a la brevedad posible.
        </p>
    </div>

    <div class="footer">
        <p>Este es un correo automático generado por el sistema de integración de API.<br>
            Hielo & Aventura - Sistema de Gestión de Reservas</p>
    </div>
</body>

</html>