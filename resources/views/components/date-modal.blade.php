@php
    $arrAccType = ['Room Only', 'Day Tour', 'Overnight'];
@endphp
@props(['id' => '', 'operation' => true])
<input type="checkbox" id="{{$id}}" class="modal-toggle" />
@if($operation)
  <div class="modal modal-bottom sm:modal-top" id="{{$id}}">
    <div class="modal-box">
      <h3 class="font-bold text-xl mb-4 md:mb-0">Choose Date</h3>
      <div class="py-4 overflow-y-scroll h-72 md:h-full">
        <section class="w-full text-error mb-8">
          <h2 class="text-sm md:text-lg">Note: Before making reservations, we must prepare the following:</h2>
          <ul type="disc" class="text-xs md:text-sm list-disc pl-10">
            <li>Downpayment through PayPal or Gcash.</li>
            <li>Present of Valid ID (Government ID)</li>
            <li>Choose date within 2 or days above from today</li>
          </ul>
        </section>
        <form class="relative " id="reservation-form" action=" {{ route('reservation.date.check') }}" method="POST">
          @csrf
          <x-select name="accommodation_type" id="accommodation_type" placeholder="Accommodation Type" :value="$arrAccType" :title="$arrAccType" />
          <div class="w-full grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-3" x-transition>
            <x-datetime-picker name="check_in" id="check_in" placeholder="Check in" class="flatpickr-reservation" value="{{ Carbon\Carbon::now()->addDays(2)->format('Y-m-d')}}" />
            <x-datetime-picker name="check_out" id="check_out" placeholder="Check out" class="flatpickr-reservation " />
            <x-input type="number" name="pax" id="pax" placeholder="Number of Guests" min="1" value="1" />
          </div> 
        </form>
      </div>
      <div class="modal-action">
        <label for="{{$id}}" class="btn btn-ghost">Close</label>
        <button class="btn btn-primary" onclick="event.preventDefault(); document.getElementById('reservation-form').submit();">Let's Check</button>
      </div>
    </div>
  </div>
@else
  <div class="modal modal-bottom sm:modal-top" id="{{$id}}">
    <div class="modal-box">
      <h3 class="font-bold text-xl py-4">Sorry, Making Reservation are temporary close</h3>
      <div class="modal-action">
        <label for="{{$id}}" class="btn btn-ghost">Close</label>
      </div>
    </div>
  </div>
@endif