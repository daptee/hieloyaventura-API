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
        <br> <br>

        Agencia: {{ $request->agency_name }} <br>
        Excursion: {{ $data->excurtion->name }} <br>
        Nro reserva: {{ $data->reservation_number ?? '-' }} <br>
        Fecha y hora: {{ $data->date->format('d/m/Y') }} - {{ $turn }} <br>
        Traslado: {{ $data->is_transfer == 1 ? "Si" : "No" }} <br>
        Nombre: {{ $request->reservation_name ?? '-' }} <br>
        Pasajeros: {{ $request->number_of_passengers }} <br>
        Hotel: {{ $data->is_transfer == 1 ? $data->hotel_name : "-" }} <br>
        Punto de encuentro: {{ $data->is_transfer == 1 ? $data->hotel_name : "Puerto Bajo de las Sombras" }} <br>
        Precio: {{ $request->price ?? '-' }}

        <br><br>
        Tarifa vigente al momento de confirmar la reserva. Puede sufrir cambios sin previo aviso.
        <br><br>

        Cualquier cosa puede contactarse con nosotros. <br>
        Contacto: reservas@hieloyaventura.com <br>
        Telefono: +54 2902 492205 o 2902-490205 de 7 a 20hs. <br>
        Las tarifas están sujetas a cambios SIN previo aviso.

        <br><br>
        Muchas gracias.

        <br><br>
        Hielo & Aventura.
    </p>
</body>

</html>