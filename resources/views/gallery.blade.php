@if(!empty($gallery))
<section class="py-6">
  <h2 class="text-3xl font-bold sm:text-4xl text-center my-5">Gallery</h2>
  <div class="container grid grid-cols-2 gap-4 p-4 mx-auto md:grid-cols-4">
    @foreach($gallery as $key => $pic)
      @if (($loop->index + 1) === 1)
        <img src="{{asset('storage/'.$pic)}}" alt="{{$key}}" class="object-cover w-full h-full col-span-2 row-span-2 rounded shadow-sm min-h-96 md:col-start-3 md:row-start-1 dark:bg-gray-500 aspect-square">
      @elseif (($loop->index + 1) === count($gallery ))
        <img src="{{asset('storage/'.$pic)}}" alt="{{$key}}" class="object-cover w-full h-full col-span-2 row-span-2 rounded shadow-sm min-h-96 md:col-start-1 md:row-start-3 dark:bg-gray-500 aspect-square">
      @else
        <img alt="" class="object-cover w-full h-full rounded shadow-sm min-h-48 dark:bg-gray-500 aspect-square" src="{{asset('storage/'.$pic)}}" alt="{{$key}}">
      @endif
    @endforeach

  </div>
</section>
@endif