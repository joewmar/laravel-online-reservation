@props(['noFooter' => false, 'DOMLoading' => false, 'btnTop' => false])
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
<body {{$DOMLoading ? 'id="main-content"' : ''}} class="bg-base-100 selection:bg-primary selection:text-base-100">
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
  @if($DOMLoading && !auth('web')->check())
    <div id="loading-screen" class="fixed inset-0 flex flex-col justify-center items-center bg-white z-[100]">
        <div class="avatar">
            <div class="w-28 rounded-full">
                <img src="{{asset('images/logo.png')}}" class="object-center" alt="Logo" />
            </div>
        </div>    
        <span class="loading loading-dots loading-lg text-primary"></span>
    </div>
  @endif
  @if($btnTop)
    <button onclick="window.scrollTo(0, 0);" type="button" id="backTop" class="hidden bottom-5 right-5 z-[70] btn btn-primary btn-circle transition-all ease-in-out">
      <i class="fa-solid fa-arrow-up"></i>
    </button>
  @endif
  @stack('top')
  {{$slot}}
  @if (!$noFooter)
    @include('partials.footer')
  @endif
  @vite('resources/js/app.js')
  @if($DOMLoading && !auth('web')->check())
    <script>
      // In your JavaScript file
      document.addEventListener("DOMContentLoaded", function () {
          // Simulate a delay (you can replace this with your actual loading logic)
          let mainContent = document.getElementById("main-content");

          setTimeout(function () {
              // Hide the loading screen
              document.getElementById("loading-screen").style.display = "none";

              // Show the main content
              mainContent.style.display = "block";
              mainContent.classList.add('transition-all');
              mainContent.classList.add('duration-500');
          }, 1000); // Replace 2000 with your desired loading time in milliseconds
      });

    </script>
  @endif
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
    AOS.init();
  </script>
  @if($btnTop)
    <script>
      let backTop = document.getElementById("backTop");
      document.addEventListener("scroll", function () {
      /*Apply classes for slide in bar*/
          scrollpos = window.scrollY;
          if (scrollpos > 50) {
              backTop.classList.remove("hidden");
              backTop.classList.add("fixed");

          } 
          else {
          //   bgNavbar.classList.add("pt-3");

              backTop.classList.remove("fixed");
              backTop.classList.add("hidden");

          }
      });
    </script>
  @endif
  @stack('scripts')
</body>
</html>