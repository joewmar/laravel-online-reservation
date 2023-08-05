<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>{{ str_replace('_', ' ', env('APP_NAME')) }}</title>
  <style>
  </style>
</head>
<body>
  <article>
      <h3 style="font-weight:bold">Dear {{$details['name']}}</h3>
      <p class="font-medium">
        {{$details['body']}}
      </p>
      <p><strong>Payment deadline</strong>  {{ $details['payment_cutoff'] }}</p>
      <a href="{{$details['link']}}">Pay Now</a>
      <p>Thank you!</p>
  </article>
</body>
</html>