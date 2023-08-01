@props(['id'=>'', 'title'=> '', 'alpinevar' => '', 'type' => 'Close', 'formID' => '', 'loader' => false])
<input type="checkbox" id="{{$id}}" class="modal-toggle" />
<div x-data="{loader: false}" class="modal modal-bottom sm:modal-middle" id="{{$id}}">
  <div class="modal-box">
    <h3 class="font-bold text-lg text-primary">{{$title}}</h3>
    <div class="my-4">
        {{$slot}}
    </div>
    <div class="modal-action">
        @if($type === 'YesNo')
            @if ($loader == true)
              <label for="{{$id}}" @click="loader = true" onclick="event.preventDefault(); document.getElementById('{{$formID}}').submit();" class="btn btn-primary">Yes</label>
            @else
              <label for="{{$id}}" onclick="event.preventDefault(); document.getElementById('{{$formID}}').submit();" class="btn btn-primary">Yes</label>
            @endif
            <label for="{{$id}}" class="btn">No</label>
        @else
          <label for="{{$id}}" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2 text-xl text-neutral" @click="{{$alpinevar}} = '' ">✕</label>
        @endif
    </div>
  </div>
  <x-loader />
</div>
