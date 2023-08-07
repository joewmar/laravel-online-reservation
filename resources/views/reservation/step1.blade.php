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
                    @if(session()->has('ck') && session('ck')  === true)
                        <label for="step2" class="btn btn-primary">
                            <span class="hidden md:inline">Proceed</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </label>
                        <x-modal id="step2" title="Before to proceed" >
                            <p class="py-4 text-error"><strong>Note:</strong> When making an online reservation, it is required to pay for downpayment.</p>
                            <p class="py-4"><strong>Allow To Pay for Online Reservation</strong></p>
                            <p>
                                <ul class="marker:text-primary">
                                    <li>Gcash</li>
                                    <li>PayPal</li>
                                </ul>
                            </p>
                            <div class="modal-action">
                                <button @click="loader = true" type="submit" class="btn btn-primary">Continue</button>
                            </div>
                        </x-modal>
                    @else
                        <button @click="loader = true" class="btn btn-primary">
                            <x-loader />
                            <span class="hidden md:inline">Check</span>
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </x-full-content>
</x-landing-layout>