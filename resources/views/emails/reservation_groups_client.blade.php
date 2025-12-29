<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Solicitud de grupos</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #333;
        }
    </style>
</head>
<body>

    <p>
        Hola, te dejamos un resumen de la solicitud de grupos nro <strong>{{ $id_solicitud }}</strong> que has realizado.
    </p>

    <p>
        Los datos de la solicitud son:<br><br>

        <strong>Excursión:</strong> {{ $mailData['nombreExcursion'] ?? 'N/D' }}<br>
        <strong>Turno:</strong> {{ $mailData['turno'] ?? 'N/D' }}<br>
        <strong>Nombre de la reserva:</strong> {{ $mailData['nombreReserva'] ?? 'N/D' }}<br>
        <strong>Cantidad de pasajeros:</strong> {{ $mailData['cantPasajeros'] ?? 'N/D' }}
    </p>

    <p>
        Muchas gracias.<br>
        El equipo de <strong>Hielo &amp; Aventura</strong>
    </p>

</body>
</html>