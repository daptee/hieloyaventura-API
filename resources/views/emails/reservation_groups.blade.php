<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Solicitud de grupos</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color: #333; }
    </style>
</head>
<body>
    <p>
        El Usuario {{ $mailData['nombreUsuario'] ?? 'N/D' }} de la agencia {{ $mailData['nombreAgencia'] ?? 'N/D' }} ha adjuntado archivos correspondiente a la solicitud de grupos nro. {{ $id_solicitud }}.
    </p>

    <p>
        Los datos de la solicitud son:<br>
        Excursion: {{ $mailData['nombreExcursion'] ?? 'N/D' }}<br>
        Turno: {{ $mailData['turno'] ?? 'N/D' }}<br>
        Nombre reserva: {{ $mailData['nombreReserva'] ?? 'N/D' }}<br>
        Cantidad de pasajeros: {{ $mailData['cantPasajeros'] ?? 'N/D' }}
    </p>

    <p>
        Muchas gracias.<br>
        El equipo de Hielo & Aventura
    </p>

</body>
</html>