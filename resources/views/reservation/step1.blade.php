@push('styles')

@endpush
<x-landing-layout>
    <x-full-content>
        <div class="flex justify-center items-center w-full h-screen">
            <div class="md:border border-primary flex flex-col justify-around w-full md:w-[50%] h-full md:h-[80%] md:rounded-xl md:shadow-lg md:shadow-primary p-10">
                <form action="{{ route('reservation.date.check')}}" method="post">
                    @csrf
                    <div class="w-auto text-center">
                        <h2 class="font-bold text-3xl uppercase">Choose your Date</h2>
                    </div>
                    <div class="w-auto text-center flex space-x-4">
                        <x-datetime-picker name="check_in" id="check_in" placeholder="Check in" class="flatpickr-reservation" />
                        <x-datetime-picker name="check_out" id="check_out" placeholder="Check out" class="flatpickr-reservation flatpickr-input2" />
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
        </div>
    </x-full-content>

</x-landing-layout>