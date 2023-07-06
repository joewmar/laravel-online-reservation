@props(['type', 'message' => ''])
<div x-show="open" x-data="{open: true}" id="close" class="fixed top-0 flex justify-center z-[100] w-full" x-init="setTimeout(() => { open = false }, 10000)">
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
        <button x-on:click="open = false" class=" btn btn-sm md:btn-circle btn-ghost">
            <i class="hidden md:inline fa-solid fa-xmark text-md"></i>
            <span class="inline md:hidden">CLOSE</span>
        </button>
    </div>
  </div>

