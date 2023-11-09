@php
    $arrAccType = ['Room Only', 'Day Tour', 'Overnight'];
    $arrAccTypeTitle = ['Room Only', 'Day Tour (Only 1 Day)', 'Overnight (Only 2 days and above)'];
    $arrPayment = ['Walk-in', 'Other Online Booking', 'Gcash', 'Paypal', 'Bank Transfer'];
    // @if()
    // @endif
    // $arrStatus = [0 => "Pending", 1 => 'Confirmed', 2 => 'Check-in', 3 => "Check-out"];
@endphp
<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Edit Reservation of {{$rlist->userReservation->name()}} (Room Assign)" back="{{route('system.reservation.show', encrypt($rlist->id))}}">
        <section class="w-full px-5 md:px-16">
        <form class="my-5"  action="{{route('system.reservation.edit.step2.store', encrypt($rlist->id))}}" method="post">
            @csrf
            <div class="my-5">
                <h1 class="font-bold">Type: <span class="font-normal">{{$roomInfo['at']}}</span></h1>
                <h1 class="font-bold">Pax: <span class="font-normal">{{$roomInfo['px']}}</span></h1>
            </div>
            @if ($roomInfo['st'] > 0 && $roomInfo['st'] < 3)
                <x-rooms id="infomdl" :rooms="$rooms" haveRate :rates="$rates" :rlist="$rlist" :reserved="$reserved" :selected="$roomInfo['rm']" includeID="{{$rlist->id}}" rateSelected="{{$roomInfo['rt']}}" />
            @endif
            <div class="flex justify-end space-x-2 mt-5">
                <a href="{{route('system.reservation.edit.step1', encrypt($rlist->id))}}" class="btn btn-ghost" @click="loader = true">Back</a>
                <button class="btn btn-primary" @click="loader = true">Next</button>
            </div>
        </form>
    </section>
  </x-system-content>
  @push('scripts')
        <script type="module" src="{{Vite::asset('resources/js/flatpickr2.js')}}"></script>
  @endpush
</x-system-layout>