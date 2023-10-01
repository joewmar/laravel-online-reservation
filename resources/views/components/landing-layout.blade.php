@props(['noFooter' => false])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{asset('images/logo.png')}}" type="image/x-icon"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="author" content="Alvin Bognot">
    <meta name="description" content="is a business that offer accommodation and tours business for the needs of a foreign or local tourist">
    <meta name="keywords" content="Online Reservation, Mt. Pinatubo, Tour, Guesthouse">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" /> 
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    @vite('resources/css/app.css')
    @stack('styles')
    <title>{{ str_replace('_', ' ', config('app.name'))}}</title>
</head>
<body class="bg-base-100 selection:bg-primary selection:text-base-100">
  @if(session()->has('success'))
    @if(is_array(session('success')))
      <x-alert type="success" :message="session('success')"/>
    @else
      <x-alert type="success" message="{{session('success')}}"/>
    @endif
  @endif
  @if(session()->has('error'))
    @if(is_array(session('error')))
      <x-alert type="error" :message="session('error')" />
    @else
      <x-alert type="error" message="{{session('error')}}" />
    @endif
  @endif
  @if(session()->has('info'))
    @if(is_array(session('info')))
      <x-alert type="info" :message="session('info')" />
    @else
      <x-alert type="info" message="{{session('info')}}" />
    @endif
  @endif
  {{$slot}}
  @if (!$noFooter)
    @include('partials.footer')
  @endif
  @vite('resources/js/app.js')
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
    AOS.init();
  </script>
  @stack('scripts')
</body>
</html>