@php
    $dateInfo = [
        'at' =>    request('at')  ? decrypt(request('at')) : old('accommodation_type'),
        'cin' =>   request('cin') ? decrypt(request('cin')) : old('check_in') ?? Carbon\Carbon::now()->format('Y-m-d'),
        'px' =>   request('px') ? decrypt(request('px')) : old('pax'),
        'cout' =>  request('cout') ? decrypt(request('cout')) : old('check_out'),
    ];
    if(session()->has('rinfo')){
        $dateInfo = [
            'at' => isset(session('rinfo')['at']) ? decrypt(session('rinfo')['at']) : old('accommodation_type'),
            'cin' => isset(session('rinfo')['cin']) ? decrypt(session('rinfo')['cin']) : old('check_in') ?? Carbon\Carbon::now()->format('Y-m-d'),
            'px' =>   request('px') ? decrypt(request('px')) : old('pax'),
            'cout' => isset(session('rinfo')['cout']) ? decrypt(session('rinfo')['cout']) : old('check_out'),
        ];
        session(['ck' => true]);
    }
    $arrAccType = ['Room Only', 'Day Tour', 'Overnight'];
    $arrAccTypeTitle = ['Room Only', 'Day Tour (Only 1 Day)', 'Overnight (Only 2 days and above)'];


@endphp
<x-landing-layout noFooter>
    <x-full-content>
        <div x-data="{at: '{{$dateInfo['at']}}'}" class="flex flex-col justify-center items-center w-full h-screen">
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
                    <a href="{{route('home')}}" class="btn btn-ghost">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span class="hidden md:inline">Home</span>
                    </a>
                    @if(session()->has('ck') && session('ck')  === true)
                        <button class="btn btn-primary">
                            <span class="hidden md:inline">Proceed</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>

                    @else
                        <button @click="loader = true" class="btn btn-primary">
                            <x-loader />
                            <span class="inline">Check</span>
                        </button>
                    @endif
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