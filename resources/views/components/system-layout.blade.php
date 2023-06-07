@props(['activeSb'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="viewport-fit=cover"> 
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/css/app.css'])
    @vite(['resources/js/app.js'])
    <title>{{ str_replace('_', ' ', config('app.name'))}}</title>
    <style>
      body{
        border: red dashed 1px;
      }
    </style>
</head>
<body class="bg-white h-screen">
  <div class="flex h-full flex-row bg-gray-100 text-gray-800 relative">
    <div class="flex-grow-0">
      <x-sidebar :active="$activeSb" />
    </div>        
    <main class="main flex flex-grow flex-col transition-all duration-150 ease-in-out md:ml-0 overflow-y-auto h-full">
      <x-system-navbar />
      {{$slot}}
    </main>
   </div>
  <script src="{{Vite::asset("resources/js/system-navbar.js")}}"></script>
</body>
</html>