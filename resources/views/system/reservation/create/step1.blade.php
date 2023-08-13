@php
    $roomInfo = [
        'rm' => session()->has('rinfo') ? decrypt(session('rinfo')['rm']) : old('room_pax'),
        'rt' => session()->has('rinfo') ? decrypt(session('rinfo')['rt']) : old('room_rate'),
    ];
@endphp

<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Add Book">
    <section class="w-full">
        <form x-data="{loader: false}" action="{{route('system.reservation.store.step.one')}}" method="post">
            @csrf
            <x-loader />
            <h2 class="text-2xl font-semibold my-5">Choose the room</h2>
            <div  class="form-control w-full">
                <label for="room_rate" class="w-full relative flex justify-start rounded-md border border-base-200 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary ">
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
                                <option value="{{encrypt($rate->id)}}" selected>{{$rate->name}} ({{$rate->occupancy}} pax)</option>
                            @else
                                <option value="{{encrypt($rate->id)}}">{{$rate->name}} ({{$rate->occupancy}} pax)</option>
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
            {{-- <div x-data="{rooms: [1, 2]}" class="flex flex-wrap justify-center md:justify-normal flex-grow m-5 gap-5 w-full"> --}}
            <div x-data="{rooms: {{$roomInfo['rm'] ? '[' . implode(',', array_keys($roomInfo['rm'])) .']' : '[]'}}, allCount: 0}" class="flex flex-wrap justify-center md:justify-normal flex-grow m-5 gap-5 w-full">
                <h2 class="text-lg font-semibold">Room Guest choosen: <span x-text="allCount"></span> guest</h2>
                @forelse ($rooms as $key => $item)
                    <div id="{{$item->availability == 1 ? 'disabledAll' : ''}}">
                        @if($item->availability == 1)
                            <input x-effect="rooms = rooms.map(function (x) { return parseInt(x, 10); });" type="checkbox" x-model="rooms" value="{{$item->id}}" id="RoomNo{{$item->room_no}}" class="peer hidden [&:checked_+_label_span]:h-full [&:checked_+_label_span_h4]:block [&:checked_+_label_span_div]:block" disabled/>
                        @else
                            <input x-effect="rooms = rooms.map(function (x) { return parseInt(x, 10); });" type="checkbox" x-model="rooms" value="{{$item->id}}" id="RoomNo{{$item->room_no}}" class="peer hidden [&:checked_+_label_span]:h-full [&:checked_+_label_span_h4]:block [&:checked_+_label_span_div]:block" x-on:checked="rooms.includes({{$item->id}})" />
                        @endif
                        <label for="RoomNo{{$item->room_no}}">
                            <div class="relative w-52 overflow-hidden rounded-lg border p-4 sm:p-6 lg:p-8 {{$item->availability == 1 ? 'opacity-70 bg-red-600' : 'border-primary cursor-pointer'}}">
                                @if($item->availability == 1)
                                    <span class="absolute inset-x-0 bottom-0 h-full  bg-red-500 opacity-80 flex items-center"><h4 class="text-base-100 block font-medium w-full text-center">Reserved</h4></span>
                                @else
                                    <span class="absolute inset-x-0 bottom-0 h-3 bg-primary flex flex-col items-center justify-center">
                                        <h4 class="text-primary-content hidden font-medium w-full text-center">Room No. {{$item->room_no}} Selected</h4> 
                                        <div x-data="{count: {{isset($roomInfo['rm'][$item->id]) ? (int)$roomInfo['rm'][$item->id] : 1}}}" class="join hidden">
                                            <button @click="count > 1 ? count-- : count = 1" type="button" class="btn btn-accent btn-xs join-item rounded-l-full">-</button>
                                            <input x-model="count" type="number" :name="rooms.includes({{$item->id}}) ? 'room_pax[{{$item->id}}]' : '' " class="input input-bordered w-10 input-xs input-accent join-item" min="1" max="{{$item->room->max_occupancy}}" @input="allCount = count" readonly/>
                                            <button @click="count < {{$item->room->max_occupancy}} ? count++ : count = {{$item->room->max_occupancy}}" type="button" class="btn btn-accent btn-xs last:-item rounded-r-full">+</button>
                                        </div>
                                    </span>
                                @endif
                                <div class="sm:flex sm:justify-between sm:gap-4">
                                    <div>
                                        <h3 class="text-lg font-bold text-neutral sm:text-xl">Room No. {{$item->room_no}}</h3>
                                        <p class="mt-1 text-xs font-medium text-gray-600">{{$item->room->name}}</p>
                                        <p class="mt-1 text-xs font-medium text-gray-600">{{$item->room->min_occupancy}} up to {{$item->room->max_occupancy}} capacity</p>
                                        @if($item->getAllPax() === (int)$item->room->max_occupancy - 1)
                                            <p class="mt-1 text-xs font-bold text-red-400">There is only {{$item->getAllPax()}} guest</p>
                                        @else
                                            <p class="mt-1 text-sm font-medium ">{{$item->getAllPax() > 0 ? $item->getAllPax() . ' guest availed' : 'No guest'}} </p>
                                        @endif
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
</x-system-layout>