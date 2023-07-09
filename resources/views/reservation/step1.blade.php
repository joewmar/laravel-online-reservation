@php
    $arrAccType = ['Room Only', 'Day Tour', 'Overnight'];
@endphp
<x-landing-layout>
    <x-full-content>
        <div class="flex flex-col justify-center items-center w-full h-screen">
            @if(session()->has('ck') && session('ck') === true)
                <form action="{{ route('reservation.date.check.store')}}" method="post">
            @else
                <form id="reservation-form" action=" {{ route('reservation.date.check') }}" method="POST">
            @endif
                @csrf
                <div class="w-auto text-center">
                    <h2 class="font-bold text-3xl uppercase">Choose your Date</h2>
                </div>
                <div class="mt-8">
                    <x-select name="accommodation_type" id="accommodation_type" placeholder="Accommodation Type" :value="$arrAccType" :title="$arrAccType" selected="{{$at ?? old('accommodation_type')}}" />
                    <div class="w-auto text-center flex space-x-4 ">
                        <x-datetime-picker name="check_in" id="check_in" placeholder="Check in" class="flatpickr-reservation" value="{{$cin ?? ''}}"/>
                        <x-datetime-picker name="check_out" id="check_out" placeholder="Check out" class="flatpickr-reservation flatpickr-input2" value="{{$cout ?? ''}}" />
                        <x-input type="number" name="pax" id="pax" placeholder="Number of Guests" value="{{$px ?? ''}}"/>
                    </div>
                </div>

                <div class="w-auto items-center flex justify-between">
                    <a href="{{route('home')}}" class="btn btn-ghost">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span class="hidden md:inline">Go Home</span>
                    </a>
                    <button class="btn btn-primary">
                        @if(session()->has('ck') && session('ck')  === true)
                            <span class="hidden md:inline">Proceed</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        @else
                            <span class="hidden md:inline">Check</span>
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </x-full-content>
</x-landing-layout>