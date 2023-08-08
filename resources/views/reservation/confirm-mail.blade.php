<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>{{ str_replace('_', ' ', env('APP_NAME')) }}</title>
  <style>
    body {
      background: #409122;
      color: #ffffff;
    }
  </style>
</head>
<body>
  <div>
    <article>
        <h3 style="font-weight: font-bold">Dear {{$details['name']}}</h3>
        <h1>{{ $details['title'] }}</h1>
        <p>{{ $details['body'] }}</p>
        <p><strong>Name:</strong> {{ $details['name'] }}</p>
        <p><strong>Age:</strong>  {{ $details['age'] }}</p>
        <p><strong>Nationality:</strong>  {{ $details['nationality'] }}</p>
        <p><strong>Country:</strong>  {{ $details['country'] }}</p>
        <p><strong>Check-in:</strong>  {{ $details['check_in'] }}</p>
        <p><strong>Check-out:</strong>  {{ $details['check_out'] }}</p>
        <p><strong>Guest</strong>  {{ $details['pax'] }}</p>
        <p><strong>Guest going on tour</strong>  {{ $details['tour_pax'] }}</p>
        <p><strong>Type:</strong>  {{ $details['accommodation_type'] }}</p>
        <p><strong>Room No:</strong>  {{ $details['room_no'] }}</p>
        <p><strong>Room Type:</strong>  {{ $details['room_type'] }}</p>
        <h3>Menu</h3>
        @if($details['menu'] != null)
          <ul>
              @foreach ($details['menu'] as $item)
                  <li>{{ $item['title'] }} : {{ $item['price'] }} = {{$item['amount']}}</li>
              @endforeach
          </ul>
        @else
          <ul>
              <li>No Tour Services</li>
          </ul>
        @endif
        <p><strong>Room Rate: </strong>  {{ $details['room_rate'] }}</p>
        <p><strong>Total: </strong>  {{ $details['total'] }}</p>
        <p><strong>Payment Method: </strong>  {{ $details['payment_method'] }}</p>
        <p>If you want a receipt: <a href="{{$details['receipt_link'] ?? '#'}}">View<a></p>
        <p>And</p>
        <p>If you want downpayment: <a href="{{$details['payment_link'] ?? '#'}}">Click here<a></p>
        <p>Payment Deadline: {{$details['payment_cutoff'] ?? ''}}</p>
        <p>Note: You need to pay a downpayment for the reservation fee. If you fail to make the payment or , your reservation will be automatically canceled.</p>
        <p>Thank you</p>
    </article>
  </div>
</body>
</html>