@props(['rating', 'name', 'comment' => '', 'id' => ''])
<article class="rounded-lg border border-neutral p-4 shadow-sm transition hover:shadow-lg sm:p-6" >
  <div class="flex items-center rounded p-2 text-white">

    <div class="rating">
      @for($count = 1;  $count <= 5; $count++)
        @if ($count <= $rating)
          <input type="radio" name="rating-2" class="mask mask-star-2 bg-orange-400 cursor-default" disabled/>
        @else
          <input type="radio" name="rating-2" class="mask mask-star-2 cursor-default" disabled/>
        @endif
      @endfor
    </div>
    <div class="ml-3">
      @if($rating == 1)
        <h1 class="font-medium text-neutral text-sm">Very Dissatisfied</h1>
      @elseif($rating == 2)
        <h1 class="font-medium text-neutral text-sm">Dissatisfied</h1>
      @elseif($rating == 3)
        <h1 class="font-medium text-neutral text-sm">Neutral</h1>
      @elseif($rating == 4)
        <h1 class="font-medium text-neutral text-sm">Satisfied</h1>
      @elseif($rating == 5)
        <h1 class="font-medium text-neutral text-sm">Very Satisfied</h1>
      @endif
    </div>
  </div>

  <a href="{{isset($id) ? route('system.reservation.show', encrypt($id)) : '#'}}">
    <h3 class="mt-0.5 text-lg font-medium text-gray-900">
      {{$name}}
    </h3>
  </a>

  <p class="mt-2 text-sm leading-relaxed text-gray-500 line-clamp-3">
    {{$comment}}
  </p>
</article>
