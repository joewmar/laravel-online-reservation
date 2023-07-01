@props(['id'=>'', 'title'=> '', 'alpinevar' => ''])

<input type="checkbox" id="{{$id}}" class="modal-toggle" />
<div class="modal modal-bottom sm:modal-middle" id="{{$id}}">
  <div class="modal-box">
    <h3 class="font-bold text-lg">{{$title}}</h3>
    <div class="my-4">
        {{$slot}}
    </div>
    <div class="modal-action">
        <label for="{{$id}}" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2 text-xl" @click="{{$alpinevar}} = '' ">✕</label>
    </div>
  </div>
</div>