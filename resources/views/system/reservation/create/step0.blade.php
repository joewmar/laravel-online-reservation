@php
    $arrAccType = ['Room Only', 'Day Tour', 'Overnight'];
    $arrAccTypeTitle = ['Room Only (Any Date)', 'Day Tour (Only 1 Day)', 'Overnight (Only 2 days and above)'];
    $roomInfo = [
        'at' =>    request('at')  ? decrypt(request('at')) : old('accommodation_type'),
        'cin' =>   request('cin') ? decrypt(request('cin')) : old('check_in'),
        'cout' =>  request('cout') ? decrypt(request('cout')) : old('check_out'),
        'px' =>  request('px') ? decrypt(request('px')) : old('pax'),
    ];
    if(session()->has('nwrinfo')){
        $roomInfo = [
            'at' => isset(session('nwrinfo')['at']) ? decrypt(session('nwrinfo')['at']) : old('accommodation_type'),
            'cin' => isset(session('nwrinfo')['cin']) ? decrypt(session('nwrinfo')['cin']) : old('check_in'),
            'cout' => isset(session('nwrinfo')['cout']) ? decrypt(session('nwrinfo')['cout']) : old('check_out'),
            'px' => isset(session('nwrinfo')['px']) ? decrypt(session('nwrinfo')['px']) : old('pax'),
        ];
    }
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
                    <x-input id="pax" name="pax" placeholder="Number of Guest" value="{{$roomInfo['px']}}" />
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