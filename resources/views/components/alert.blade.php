@props(['type', 'message' => ''])
<div x-show="open" x-data="{open: true}" id="close" class="fixed top-0 flex justify-center z-[100] w-full" x-init="setTimeout(() => { open = false }, 10000)">
    <div class="w-full alert alert-{{$type}} rounded-sm shadow-md">
        @if(Str::lower($type) == 'success')
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        @elseif(Str::lower($type) == 'error')
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        @elseif(Str::lower($type) == 'info')
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        @endif
        <div>
            @if(is_array($message))
                <ul>
                    @foreach($message as $item)
                        <li>{{$item}}</li>
                    @endforeach
                </ul>
            @else
                <span class="text-md font-semibold">{{$message}}</span>
            @endif
        </div>
        <button x-on:click="open = false" class=" btn btn-sm md:btn-circle btn-ghost">
            <i class="hidden md:inline fa-solid fa-xmark text-md"></i>
            <span class="inline md:hidden">CLOSE</span>
        </button>
    </div>
  </div>

