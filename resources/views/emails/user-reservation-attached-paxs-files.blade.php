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
        @foreach ($paxs as $pax)
        <div>
            <ul>
                    <li> {{ "Nombre y apellido: " . $pax->name ?? '-' }} </li>
                    <li> {{"DNI: " . $pax->dni ?? '-' }} </li>
                    <li> {{"Nacionalidad : " . $pax->nacionality->name ?? '-' }} </li>
                    <li> {{"Fecha de nacimiento : " . $pax->birth_date ?? '-' }} </li>
            </ul>
        </div>
    @endforeach
    </p>
</body>
</html>