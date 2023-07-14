@props(['activeSb'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="viewport-fit=cover"> 
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/css/app.css'])
    @vite(['resources/js/app.js'])
    @stack('styles')
    <title>{{ str_replace('_', ' ', config('app.name'))}}</title>
    {{-- <script src="//unpkg.com/alpinejs" defer></script> --}}
</head>
<body class="bg-white h-screen">
  @if(session()->has('success'))
    @if(is_array(session('success')))
      <x-alert type="success" :message="session('success')"/>
    @else
      <x-alert type="success" message="{{session('success')}}"/>
    @endif
  @elseif(session()->has('error'))
    @if(is_array(session('error')))
      <x-alert type="error" :message="session('error')"/>
    @else
      <x-alert type="error" message="{{session('error')}}"/>
    @endif
  @endif
  <div x-data="{open: true}" class="flex h-full flex-row bg-gray-100 text-gray-800 relative">
    <div class="flex-grow-0">
      <x-sidebar :active="$activeSb" />
    </div>        
    
    <main class="main flex flex-grow flex-col transition-all duration-150 ease-in-out md:ml-0 overflow-y-auto h-full">
      <div id="overlay" class="transition ease-in-out duration-300 hidden fixed w-full h-full bg-primary bg-opacity-70 z-50"></div>
      <x-system-navbar />
      <div class="mt-10 md:mt-24">
        {{$slot}}
      </div>
    </main>
   </div>
   @stack('scripts')
  <script src="{{Vite::asset("resources/js/system-navbar.js")}}"></script>
</body>
</html>