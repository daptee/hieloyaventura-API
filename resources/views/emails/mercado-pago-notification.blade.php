<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Notification - Mercado Pago</title>
</head>
<body>
    <p>
        Se ha recibido notificación por parte de Mercado Pago sobre la reserva nro: <strong style="font-size: 16px">{{ $order_number }}</strong>.<br>
        <br>
        La misma informa que el pago con el nro <strong style="font-size: 16px">{{ $payment_number }}</strong> se encuentra ahora en estado <strong style="font-size: 16px">{{ "$payment_status" }}</strong>.
    </p>
</body>
</html>