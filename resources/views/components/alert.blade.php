@props(['type', 'message' => ''])
<div id="close" class="fixed top-0 flex justify-center z-[100] w-full">
    <div class="w-96 alert alert-{{$type}} shadow-md">
        @if(Str::lower($type) == 'success')
            <i class="fa-solid fa-check text-xl"></i>        
        @elseif(Str::lower($type) == 'error')
            <i class="fa-solid fa-xmark text-xl"></i>
        @elseif(Str::lower($type) == 'info')
            <i class="fa-solid fa-info text-xl"></i>        
        @endif
        <div>
            <span class="text-md font-semibold">{{$message}}</span>
        </div>
        <button onclick="alertClose()" class="block md:hidden btn btn-sm btn-ghost">CLOSE</button>
        <button onclick="alertClose()" class="hidden md:block btn btn-circle btn-ghost"><i class="fa-solid fa-xmark text-md"></i></button>
    </div>
  </div>
  <script src="{{Vite::asset("resources/js/click-close.js")}}"></script>

