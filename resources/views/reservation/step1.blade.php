@push('styles')

@endpush
<x-landing-layout>
    <x-full-content>
        <div class="flex flex-col justify-center items-center w-full h-screen">
                <form action="{{ route('reservation.date.check.store')}}" method="post">
                    @csrf
                    <div class="w-auto text-center">
                        <h2 class="font-bold text-3xl uppercase">Choose your Date</h2>
                    </div>
                    <div class="mt-8">
                        <div class="w-auto text-center flex space-x-4 ">
                            <x-datetime-picker name="check_in" id="check_in" placeholder="Check in" class="flatpickr-reservation" value="{{$cin !== ''  ? $cin : ''}}"/>
                            <x-datetime-picker name="check_out" id="check_out" placeholder="Check out" class="flatpickr-reservation flatpickr-input2" value="{{$cout !== '' ? $cout : ''}}" />
                            <x-input type="number" name="pax" id="pax" placeholder="Number of Guests" value="{{$px !== ''  ? $px : ''}}"/>
                        </div>
                    </div>

                    <div class="w-auto items-center flex justify-between">
                        <a href="{{URL::previous()}}" class="btn btn-ghost">
                            <i class="fa-solid fa-arrow-left"></i>
                            <span class="hidden md:inline">Back</span>
                        </a>
                        <button class="btn btn-primary">
                            <span class="hidden md:inline">Proceed</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
            </form>
        </div>
    </x-full-content>

</x-landing-layout>