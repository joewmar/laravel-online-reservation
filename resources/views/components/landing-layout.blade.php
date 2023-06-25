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
    @vite(['resources/css/app.css'])
    @vite(['resources/js/app.js'])
    @stack('styles')
    <title>{{ str_replace('_', ' ', config('app.name'))}}</title>
</head>
<body class="bg-base-100">
  @if(session()->has('success'))
    <x-alert type="success" message="{{session('success')}}"/>
  @endif
  @if(session()->has('error'))
    <x-alert type="error" message="{{session('error')}}"/>
  @endif
  @if(session()->has('info'))
    <x-alert type="info" message="{{session('info')}}"/>
  @endif
  {{$slot}}

  @include('partials.footer')
  @stack('scripts')
</body>
</html>