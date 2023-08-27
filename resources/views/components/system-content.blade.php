@props(['title' => '', 'back' => false])
<div {{ $attributes->merge(['class' => 'h-auto items-center m-0 md:m-4 bg-base-100 shadow-md'])}}>
    <div class="w-full py-16 px-8">
        @if($back)
          <a href="{{URL::previous()}}" class="btn btn-ghost">
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