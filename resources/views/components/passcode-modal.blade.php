@props(['id'=>'', 'formId'=> '', 'title'=> ''])
<div class="modal modal-bottom sm:modal-middle" id="{{$id}}">
    <div class="modal-box">
      <h3 class="font-bold text-lg">{{$title}}</h3>
      <p class="py-4"><x-input type="password" id="passcode" name="passcode" placeholder="Enter passcode to confirm" maxlength="4" /></p>
      <div class="modal-action">
        <a href="#" for="{{$id}}" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2 text-xl">✕</a>
        <a href="#" class="btn btn-primary" onclick="event.preventDefault(); document.getElementById('{{$formId}}').submit();">Proceed</a>
      </div>
    </div>
  </div>