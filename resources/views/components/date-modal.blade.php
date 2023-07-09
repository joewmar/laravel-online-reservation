@php
    $arrAccType = ['Room Only', 'Day Tour', 'Overnight'];
@endphp
@props(['id' => ''])
<input type="checkbox" id="{{$id}}" class="modal-toggle" />
<div class="modal modal-bottom sm:modal-top" id="{{$id}}">
  <div class="modal-box">
    <h3 class="font-bold text-xl">Choose your Date</h3>
    <p class="py-4">
      <form id="reservation-form" action=" {{ route('reservation.date.check') }}" method="POST">
        @csrf
        <x-select name="accommodation_type" id="accommodation_type" placeholder="Accommodation Type" :value="$arrAccType" :title="$arrAccType" />
        <div class="w-auto text-center flex space-x-4 " x-transition>
          <x-datetime-picker name="check_in" id="check_in" placeholder="Check in" class="flatpickr-reservation"/>
          <x-datetime-picker name="check_out" id="check_out" placeholder="Check out" class="flatpickr-reservation flatpickr-input2" />
          <x-input type="number" name="pax" id="pax" placeholder="Number of Guests" />
        </div> 
      </form>
    </p>
    <div class="modal-action">
      <label for="{{$id}}" class="btn btn-ghost">Close</label>
      <button class="btn btn-primary" onclick="event.preventDefault(); document.getElementById('reservation-form').submit();">Let's Check</button>
    </div>
  </div>
</div>