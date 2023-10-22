@props(['title' => '', 'back' => ''])
<div {{ $attributes->merge(['class' => 'h-auto items-center m-0 md:m-4 bg-base-100 shadow-md'])}}>
    <div class="w-full py-16 px-3  md:px-8">
        @if(!empty($back))
          <a href="{{$back}}" class="btn btn-ghost">
            <i class="fa-solid fa-arrow-left"></i>
          </a>
        @endif
        @if(isset($title))
          <div class="mx-auto w-auto text-center">
            <h2 class="text-2xl font-bold md:text-3xl">{{$title}}</h2>
          </div>
        @endif
        {{$slot}}
    </div>
</div>