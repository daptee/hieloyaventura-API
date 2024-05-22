<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>title del html</title>
</head>
<body>
    <p>
        Han enviado una nueva solicitud para compra de excursion grupal. La informacion es la siguiente: <br>
        Excursion: {{ $data->nombre_excursion }} <br>
        Fecha y hora: {{ $data->fecha . ' ' . $data->turno }} <br>
        Con traslado / Sin traslado: {{ $data->con_o_sin_traslado == 1 ? 'Con traslado' : 'Sin traslado'}} <br>
        Cantidad de pasajeros: {{ $data->cantidad_pasajeros }} <br>
        Nombre: {{ $data->nombre_completo_persona }}<br>
        Mail de personal: {{ $data->email_de_personal }} <br>
        Tel: {{ $data->tel_persona }}<br>
        @if(isset($agency_user))
        Agencia: {{ $agency_user['agency_name'] }}<br>
        Usuario: {{ $agency_user['user_name'] }}<br>
        @endif
        <br>
        Ponerse en contacto a la brevedad.
    </p>
</body>
</html>