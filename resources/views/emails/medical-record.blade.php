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

        @foreach ($passengers as $passenger)
            <div>
                <h4>{{ $passenger['passenger_name'] }}</h4>
                @if(count($passenger['diseases']))
                    <ul>
                        @foreach ($passenger['diseases'] as $disease_name)
                            <li>{{ $disease_name }}</li>    
                        @endforeach
                    </ul>                    
                    <span>El resto de las enfermedades listadas en la web no fueron marcadas como activas.</span>
                @else
                    <ul>
                        <li>El pasajero no informó tener ninguna de las enfermedades seleccionables.</li>    
                    </ul> 
                @endif
            </div>
        @endforeach

        <br><br>
        Version ficha médica: Ficha médica revisión 2 - {{ now()->format('d/m/Y') }}
    </p>
</body>
</html>