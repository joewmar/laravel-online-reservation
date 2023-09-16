@props(['id'=>'', 'title'=> '', 'alpinevar' => null, 'type' => 'Close', 'formID' => '', 'loader' => false])
<input type="checkbox" id="{{$id}}" class="modal-toggle" />
<div x-data="{loader: false}" class="modal modal-bottom sm:modal-middle" id="{{$id}}">
  <div {{ $attributes->merge(['class'=> "modal-box"]) }}>
    <h3 class="font-bold text-lg text-primary">{{$title}}</h3>
    <div class="my-4">
        {{$slot}}
    </div>

    <div class="modal-action">
        @if($type === 'YesNo')
            @if ($loader)
              <label for="{{$id}}" @click="loader = true" onclick="event.preventDefault(); document.getElementById('{{$formID}}').submit();" class="btn btn-primary">Yes</label>
            @else
              <label for="{{$id}}" onclick="event.preventDefault(); document.getElementById('{{$formID}}').submit();" class="btn btn-primary">Yes</label>
            @endif
            <label for="{{$id}}" class="btn">No</label>
        @else
          @if(isset($alpinevar))
            <label for="{{$id}}" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2 text-xl text-neutral" @click="{{$alpinevar}} = '' ">✕</label>
          @else
            <label for="{{$id}}" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2 text-xl text-neutral">✕</label>
          @endif
        @endif
    </div>
  </div>
  <x-loader />
</div>
