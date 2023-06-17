@props(['id'=>'', 'formId'=> '', 'title'=> ''])
<input type="checkbox" id="{{$id}}" class="modal-toggle" />
<div class="modal modal-bottom sm:modal-middle" id="{{$id}}">
    <div class="modal-box">
      <h3 class="font-bold text-lg">{{$title}}</h3>
      <div class="w-full text-center my-5">
        <div id="passform" class="flex justify-center space-x-10">
          <input class="passcode-input w-16 h-16 input input-bordered input-primary" type="number" maxlength=1 autofocus/>
          <input class="passcode-input w-16 h-16 input input-bordered input-primary" type="number" maxlength=1 />
          <input class="passcode-input w-16 h-16 input input-bordered input-primary" type="number" maxlength=1 />
          <input class="passcode-input w-16 h-16 input input-bordered input-primary" type="number" maxlength=1 />
          <input id="passcode" name="passcode" type="hidden" />
        </div>
      </div>
      <div class="modal-action">
        <label for="{{$id}}" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2 text-xl">✕</label>
        <label for="passform" class="btn btn-ghost" onclick="reset()">Reset</label>
        <label for="{{$id}}" id="btn-hidden" class="hidden btn btn-primary" onclick="event.preventDefault(); document.getElementById('{{$formId}}').submit();">Proceed</label>
      </div>
    </div>
  </div>
<script src="{{Vite::asset("resources/js/passcode.js")}}"></script>
   