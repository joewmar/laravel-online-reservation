<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="viewport-fit=cover"> 
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/css/app.css'])
    @vite(['resources/js/app.js'])
    <title>{{ str_replace('_', ' ', config('app.name'))}}</title>
</head>
<body class="bg-white">
  <x-system-navbar />
  {{$slot}}

  <script src="{{Vite::asset("resources/js/system-navbar.js")}}"></script>
</body>
</html>