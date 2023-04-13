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
        Mandamos un resumen de la ficha medica correspondiente al numero de reserva: {{ $reservation_numb }} <br>
        Fecha de excursion: {{ Carbon\Carbon::createFromFormat('Y-m-d', $medical_record->excurtion_date)->format('d/m/Y') }}

        @foreach (json_decode($medical_record->passengers) as $passenger)
            <div>
                <ul>
                        <li> {{ "Nombre y apellido: $passenger->name_lastname" }} </li>
                        <li> {{"Email : $passenger->email" }} </li>
                        <li> {{"DNI/Pasaporte : $passenger->dni_passport" }} </li>
                        <li> {{"Nacionalidad : $passenger->nacionality" }} </li>
                        <li> {{"Fecha de nacimiento : $passenger->birth_date" }} </li>
                        <li> {{"Teléfono : $passenger->phone" }} </li>
                        <li> {{"Edad : $passenger->age" }} </li>
                        <li> {{"Grupo Sanguineo : $passenger->blood_type" }} </li>
                        <li> Enfermedades: <br> 
                            @foreach ($passenger->diseases as $disease)
                                 <li style="margin-left: 0.5rem; list-style:none">{{ "- " . App\Models\Disease::find($disease)->nombre }} </li> 
                            @endforeach
                        </li>
                        <li> {{"Texto adicional : $passenger->aditional_text" }} </li>
                </ul>
            </div>
        @endforeach
    </p>
</body>
</html>