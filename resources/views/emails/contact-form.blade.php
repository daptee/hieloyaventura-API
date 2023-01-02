<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Formulario de contacto</title>
</head>
<body>
    <p>
        Hola! Se ha recibido una solicitud de contacto mediante la pagina web <br><br>

        Los datos que el usuario ha ingresado son: <br> <br>
        
        Nombre completo: {{ $data->nombre_y_apellido }} <br>
        Email: {{ $data->email }} <br>
        Mensaje: {{ $data->mensaje }} <br>
    </p>
</body>
</html>