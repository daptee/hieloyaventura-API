<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Procesar CV</title>
</head>
<body>
    <p>
        El usuario {{ $data['user_name'] }} de la agencia {{ $data['agency_name'] }} ha solicitado un cambio sobre la reserva {{ $data['reservation_number'] }}.
        <br><br>
        Solicitud: {{ $data['request'] }}
        <br><br>
        Muchas gracias. <br>
        El equipo de Hielo & Aventura.
    </p>
</body>
</html>