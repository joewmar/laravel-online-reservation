@props(['title'])
<div class="h-auto items-center m-4 bg-base-100 shadow-md">
    <div class="w-full py-16 px-8">
        <div class="mx-auto w-auto text-center">
          <h2 class="text-2xl font-bold sm:text-3xl">{{$title}}</h2>
        </div>
        {{$slot}}
    </div>
</div>