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
        @foreach ($passengers as $passenger)
        <div>
            <ul>
                    <li> {{ "Nombre y apellido: " . $passenger->name ?? '-' }} </li>
                    <li> {{"DNI: " . $passenger->dni ?? '-' }} </li>
                    <li> {{"Nacionalidad : " . $passenger->nacionality->name ?? '-' }} </li>
                    <li> {{"Fecha de nacimiento : " . $passenger->birth_date ?? '-' }} </li>
            </ul>
        </div>
    @endforeach
    </p>
</body>
</html>