@php
    $arrAccType = ['Room Only', 'Day Tour', 'Overnight'];
    $arrAccTypeTitle = ['Room Only', 'Day Tour (Only 1 Day)', 'Overnight (Only 2 days and above)'];
@endphp
<x-landing-layout noFooter>
    <x-navbar activeNav="My Reservation" type="plain"/>
    <x-full-content>
        <div x-data="{at: '{{$dateInfo['at']}}'}" class="flex flex-col justify-center items-center w-full h-screen">
            <form action="{{ route('user.reservation.edit.step1.store', $id)}}" method="post">
                @csrf
                <div class="w-auto text-center">
                    <h2 class="font-bold text-3xl uppercase">Choose your Date</h2>
                </div>
                <div class="mt-8">
                    <x-select name="accommodation_type" id="accommodation_type" placeholder="Accommodation Type" :value="$arrAccType" :title="$arrAccTypeTitle" xModel="at" />
                    <div class="w-full grid grid-cols-1 gap-1 md:gap-3" :class="!(at == 'Day Tour') ? 'md:grid-cols-3' : 'md:grid-cols-2' " x-transition>
                        <x-datetime-picker name="check_in" id="check_in" placeholder="Check in" class="flatpickr-reservation" value="{{$dateInfo['cin']}}"/>
                        <div x-show="!(at == 'Day Tour')" x-transaction>
                            <x-datetime-picker name="check_out" id="check_out" placeholder="Check out" class="flatpickr-reservation" value="{{$dateInfo['cout']}}" />
                        </div>
                        <x-input type="number" name="pax" id="pax" placeholder="Number of Guests" value="{{$dateInfo['px']}}"/>
                    </div>
                </div>

                <div class="w-auto items-center flex justify-between">
                    <a href="{{route('user.reservation.show', $id)}}" class="btn btn-ghost">
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
    @php 
        $from = App\Models\WebContent::all()->first()->from ?? null; 
        $to = App\Models\WebContent::all()->first()->to ?? null; 
    @endphp

    @push('scripts')
    <script>
        @if(isset($from) && isset($to))
          const mop = {
              from: '{{\Carbon\Carbon::createFromFormat('Y-m-d', $from)->format('Y-m-d')}}',
              to: '{{\Carbon\Carbon::createFromFormat('Y-m-d', $to)->format('Y-m-d')}}'
          };
        @else
          const mop = '2001-15-30';
        @endif
        const md = '{{Carbon\Carbon::now()->addDays(2)->format('Y-m-d')}}';

    </script>
        <script type="module" src="{{Vite::asset('resources/js/flatpickr.js')}}"></script>
    @endpush
</x-landing-layout>