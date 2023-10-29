@php
    $arrAccType = ['Room Only', 'Day Tour', 'Overnight'];
    $arrAccTypeTitle = ['Room Only (Any Date)', 'Day Tour (Only 1 Day)', 'Overnight (Only 2 days and above)'];
@endphp

<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Add Book (Date)" back="{{route('system.reservation.home')}}">
    <section class="w-full px-5 md:px-16">
        <form x-data="{loader: false, at: '{{$roomInfo['at'] ?? ''}}'}" action="{{route('system.reservation.store.step.zero')}}" method="post">
            @csrf
            <x-loader />
            <h2 class="text-2xl font-semibold my-5">Choose the date</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-4">
                <div class="col-span-2">
                    <x-select xModel="at" name="accommodation_type" id="accommodation_type" placeholder="Accommodation Type" :value="$arrAccType" :title="$arrAccTypeTitle" />
                </div>
  
                <div :class="at === 'Day Tour' ? 'col-span-2' : '' ">
                    <x-datetime-picker name="check_in" id="check_in" placeholder="Check in" class="flatpickr-reservation-month" value="{{$roomInfo['cin'] }}"/>
                </div>
                <div x-show="!(at === 'Day Tour')">
                    <x-datetime-picker name="check_out" id="check_out" placeholder="Check out" class="flatpickr-reservation-month" value="{{$roomInfo['cout']}}" />
                </div>
                <div class="col-span-2">
                    <x-input type="number" id="pax" name="pax" placeholder="Number of Guest" value="{{$roomInfo['px']}}" />
                </div>
            </div>

            <div class="flex justify-end">
                <button class="btn btn-primary" @click="loader = true">Next</button>
            </div>
        </form>
    </section>
  </x-system-content>
  @push('scripts')
        <script type="module" src="{{Vite::asset('resources/js/flatpickr2.js')}}"></script>
  @endpush
</x-system-layout>