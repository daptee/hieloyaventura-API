<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Notificacion Pasajero</title>
</head>
<body>
    <p style="white-space: pre-line">Hola {{ Auth::guard('agency')->user()->name }}. Esto es un correo automatico con la confirmacion de tu reserva.        
    
        Agencia: {{ $request->agency_name }}
        Excursion: {{ $data->excurtion->name }}
        Fecha y hora: {{ $data->date->format('Y/m/d') }} - {{ $data->turn->format("H:i") }}
        Traslado: {{ $data->is_transfer == 1 ? "Si" : "No" }}
        Nombre: {{ $request->reservation_name }}
        Pasajeros: {{ $request->number_of_passengers }}

        Cualquier cosa puede contactarse con nosotros.
        Contacto: reservas@hieloyaventura.com
        Telefono: +54 2902 492205 o 2902-490205 de 7 a 20hs.
        Las tarifas están sujetas a cambios SIN previo aviso.

        Muchas gracias.

        Hielo & Aventura.
    </p>
</body>
</html>