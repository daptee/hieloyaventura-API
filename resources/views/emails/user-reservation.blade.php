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
        Gracias por realizar tu compra con nosotros. <br>
        A continuacion te dejamos adjunto un PDF con todos los datos de tu reserva. Asimismo, podes ingresar en la web y con tu usuario y contraseña descargar tambien este PDF. <br> <br>
        Si tenes algun inconveniente, podes escribirnos a info@hieloyaventura.com, o bien comunicarte con nosotros a +54 (2902) 492 205/094
        
        @if($minitrekking_o_bigice)
            <br> <br>
            Debido a que tu excursion presenta dificultades fisicas, te solicitamos por favor que completes una ficha medica de todos los pasajeros, dentro del siguiente link: <a href="https://hya.daptee.com.ar/ficha-medica/{{ $hash_reservation_number }}">https://hya.daptee.com.ar/ficha-medica/{{ $hash_reservation_number }}</a>
        @endif
    </p>
</body>
</html>