@php
    $arrAccType = ['Room Only', 'Day Tour', 'Overnight'];
    $arrAccTypeTitle = ['Room Only (Any Date)', 'Day Tour (Only 1 Day)', 'Overnight (Only 2 days and above)'];
    $arrPayment = ['Walk-in', 'Other Online Booking', 'Gcash', 'Paypal'];
    $arrStatus = [1 => 'Confirmed', 2 => 'Check-in', 3 =>'Check-out'];
    $roomInfo = [
        'at' =>    request('at')  ? decrypt(request('at')) : old('accommodation_type'),
        'px' =>    request('px')  ? decrypt(request('px')): old('pax'),
        'rm' =>    request('rm')  ? decrypt(request('rm')): old('room_pax'),
        'rt' =>    request('rt')  ? decrypt(request('rt')) : old('room_rate'),
        'cin' =>   request('cin') ? decrypt(request('cin')) : old('check_in'),
        'cout' =>  request('cout') ? decrypt(request('cout')) : old('check_out'),
        'py' =>   request('py') ? decrypt(request('py')) : old('payment_method'),
        'tpx' =>   request('tpx') ? decrypt(request('tpx')) : old('tour_pax'),
        'st' =>   request('st') ? decrypt(request('st')) : old('status'),
    ];
    if(auth('system')->user()->type == 2){
        $arrPayment = ['Walk-in', 'Gcash', 'Paypal'];
        $arrStatus = ['Check-in', 'Check-out'];
        $roomInfo['cin'] = request('cin') ? decrypt(request('cin')) : (old('check_in') ?? Carbon\Carbon::now('Asia/Manila')->format('Y-m-d'));
    }
    if(session()->has('nwrinfo')){
        $roomInfo = [
            'at' => isset(session('nwrinfo')['at']) ? decrypt(session('nwrinfo')['at']) : old('accommodation_type'),
            'px' => isset(session('nwrinfo')['px']) ? decrypt(session('nwrinfo')['px']) : old('pax'),
            'rm' => isset(session('nwrinfo')['rm']) ? decrypt(session('nwrinfo')['rm']) : old('room_pax'),
            'rt' => isset(session('nwrinfo')['rt']) ? decrypt(session('nwrinfo')['rt']) : old('room_rate'),
            'cin' => isset(session('nwrinfo')['cin']) ? decrypt(session('nwrinfo')['cin']) : old('check_in'),
            'cout' => isset(session('nwrinfo')['cout']) ? decrypt(session('nwrinfo')['cout']) : old('check_out'),
            'py' => isset(session('nwrinfo')['py']) ? decrypt(session('nwrinfo')['py']) : old('payment_method'),
            'tpx' => isset(session('nwrinfo')['tpx']) ? decrypt(session('nwrinfo')['tpx']) : old('tour_pax'),
            'st' => isset(session('nwrinfo')['st']) ? decrypt(session('nwrinfo')['st']) : old('status'),
        ];
    }


@endphp

<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Add Book (Room Assign)">
    <section class="w-full px-5 md:px-16">
        <form x-data="{loader: false, npax: {{$roomInfo['px'] ?? 1}}, at: ''}" action="{{route('system.reservation.store.step.one')}}" method="post">
            @csrf
            <x-loader />
            <h2 class="text-2xl font-semibold my-5">Choose the room</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-4">
                <x-select xModel="at" name="accommodation_type" id="accommodation_type" placeholder="Accommodation Type" :value="$arrAccType" :title="$arrAccTypeTitle" />
                <div class="form-control w-full">
                    <label for="room_rate" class="w-full relative flex justify-start rounded-md border border-gray-400 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary @error('room_rate') border-error @enderror">
                            <select name="room_rate" id="room_rate" class='w-full select select-primary peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0'>
                            <option value="" disabled selected>Please select</option>
                            @foreach ($rates as $key => $rate)
                            @php
                                try {
                                    $rateID = decrypt($rate->id);
                                } catch (Exception $e) {
                                    $rateID = $rate->id;
                                }
                            @endphp
                                @if($roomInfo['rt'] == $rateID);
                                    <option value="{{encrypt($rate->id)}}" :selected="npax == {{$rate->occupancy}} || npax > {{$rate->occupancy}}">{{$rate->name}} ({{$rate->occupancy}} pax)</option>
                                @else
                                    <option value="{{encrypt($rate->id)}}" :selected="npax == {{$rate->occupancy}} || npax > {{$rate->occupancy}}">{{$rate->name}} ({{$rate->occupancy}} pax)</option>
                                @endif
                            @endforeach
                        </select>        
                        <span id="room_rate" class="pointer-events-none absolute start-2.5 top-0 -translate-y-1/2 bg-white p-0.5 text-xs text-neutral transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-0 peer-focus:text-xs">
                            Room Type
                        </span>
                    </label>
                    <label class="label">
                        <span class="label-text-alt">
                            @error('room_rate')
                                <span class="label-text-alt text-error">{{$message}}</span>
                            @enderror
                        </span>
                    </label>
                </div> 
                <div :class="at === 'Day Tour' || at === 'Overnight' ? '' : 'col-span-1 md:col-span-2'">
                    <x-input id="pax" name="pax" placeholder="Number of Guest" xModel="npax" />
                </div>
                <template x-if="at === 'Day Tour' || at === 'Overnight'">
                    <x-input type="number" name="tour_pax" id="tour_pax" placeholder="How many people will be going on the tour" value="{{$roomInfo['tpx']}}" />
                </template>
                <div :class="at === 'Day Tour' ? 'col-span-2' : '' ">
                    <x-datetime-picker name="check_in" id="check_in" placeholder="Check in" class="flatpickr-reservation-month" value="{{$roomInfo['cin'] }}"/>

                </div>
                <template x-if="!(at === 'Day Tour')">
                    <x-datetime-picker name="check_out" id="check_out" placeholder="Check out" class="flatpickr-reservation-month" value="{{$roomInfo['cout']}}" />
                </template>
                <x-select id="payment_method" name="payment_method" placeholder="Payment Method" :value="$arrPayment"  :title="$arrPayment" selected="{{$roomInfo['py']}}"/>
                <x-select id="status" name="status" placeholder="Status" :value="array_keys($arrStatus)"  :title="array_values($arrStatus)" selected="{{$arrStatus[$roomInfo['st']] ?? ''}}"/>
            </div>

            <div x-data="{rooms: {{$roomInfo['rm'] ? '[' . implode(',', array_keys($roomInfo['rm'])) .']' : '[]'}}}" class="flex justify-center flex-wrap gap-5 w-full">
                @forelse ($rooms as $key => $item)
                    <div>
                        <input x-ref="RoomRef" x-effect="rooms = rooms.map(function (x) { return parseInt(x, 10); });" type="checkbox" x-model="rooms" value="{{$item->id}}" id="RoomNo{{$item->room_no}}" class="peer hidden [&:checked_+_label_span]:h-full [&:checked_+_label_span_h4]:block [&:checked_+_label_span_div]:block" x-on:checked="rooms.includes({{$item->id}})" />
                        <label for="RoomNo{{$item->room_no}}">
                            <div class="relative w-52 overflow-hidden rounded-lg border p-4 sm:p-6 lg:p-8 border-primary cursor-pointer">
                                <span class="absolute inset-x-0 bottom-0 h-3 bg-primary flex flex-col items-center justify-center">
                                    <h4 class="text-primary-content hidden font-medium w-full text-center">Room No. {{$item->room_no}} Selected</h4> 
                                    <div  x-data="{count: {{isset($roomInfo['rm'][$item->id]) ? (int)$roomInfo['rm'][$item->id] : 1}}}" class="join hidden">
                                        <button  @click="count > 1 ? count-- : count = 1" type="button" class="btn btn-accent btn-xs join-item rounded-l-full">-</button>
                                        <input x-model="count" type="number" :name="rooms.includes({{$item->id}}) ? 'room_pax[{{$item->id}}]' : '' " class="input input-bordered w-10 input-xs input-accent join-item" min="1" max="{{$item->room->max_occupancy}}"  readonly/>
                                        <button  @click="count < {{$item->room->max_occupancy}} ? count++ : count = {{$item->room->max_occupancy}}"  type="button" class="btn btn-accent btn-xs last:-item rounded-r-full">+</button>
                                    </div>
                                </span>
                                <div class="sm:flex sm:justify-between sm:gap-4">
                                    <div>
                                        <h3 class="text-lg font-bold text-neutral sm:text-xl">Room No. {{$item->room_no}}</h3>
                                        <p class="mt-1 text-xs font-medium text-gray-600">{{$item->room->name}}</p>
                                        <p class="mt-1 text-xs font-medium text-gray-600">{{$item->room->max_occupancy}} capacity</p>
                                        {{-- @if($item->getAllPax() === (int)$item->room->max_occupancy - 1)
                                            <p class="mt-1 text-xs font-bold text-red-400">There is only {{$item->getAllPax()}} guest</p>
                                        @else
                                            <p class="mt-1 text-sm font-medium ">{{$item->getAllPax() > 0 ? $item->getAllPax() . ' guest availed' : 'No guest'}} </p>
                                        @endif --}}
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                @empty
                    <p class="text-2xl font-semibold">No Room Found</p>
                @endforelse
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