<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="viewport-fit=cover"> 
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="author" content="name">
    <meta name="description" content="description here">
    <meta name="keywords" content="keywords,here">
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" integrity="sha512-SzlrxWUlpfuzQ+pcUCosxcglQRNAq/DZjVsC0lE40xsADsfeQoEypE+enwcOiGjk/bSuGGKHEyjSoQ1zVisanQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />     --}}
    <link rel="stylesheet" href="{{Vite::asset("resources/js/swiper/swiper-bundle.css")}}" />    
    @vite(['resources/css/app.css'])
    @vite(['resources/js/app.js'])
    <title>{{ str_replace('_', ' ', config('app.name'))}}</title>
</head>
<body class="bg-white">
  {{$slot}}

  @include('partials.footer')
  <script src="{{Vite::asset("resources/js/swiper/swiper-bundle.js")}}"></script>
  <script src="{{Vite::asset("resources/js/custom-slider.js")}}"></script>
  <script src="{{Vite::asset("resources/js/navbar.js")}}"></script>
</body>
</html>