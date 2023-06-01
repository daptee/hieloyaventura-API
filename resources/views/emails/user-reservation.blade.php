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
        {!! $msg !!}
        
        @if($bigice)
            <br> <br>
            {{ $msg_is_bigice }} <a href="{{ config('app.url_hya') }}/ficha-medica/{{ $hash_reservation_number }}">{{ config('app.url_hya') }}/ficha-medica/{{ $hash_reservation_number }}</a>
        @endif
    </p>
</body>
</html>