
<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Add Book (Room Assign)">
    <section class="w-full px-5 md:px-16">
        <form class="my-5" x-data="{loader: false, npax: {{$roomInfo['px']}}}" action="{{route('system.reservation.store.step.one', Arr::query(['st' => request('st') ?? '']))}}" method="post">
            @csrf
            <x-loader />
            <p class="mb-5 text-lg font-bold">Pax: <span class="font-medium">{{$roomInfo['px']}}</span></p>
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
            <div x-data="{force: false, rooms: {{!empty($roomInfo['rm'] ?? []) ? '[' . implode(',', array_keys($roomInfo['rm'])) .']' : '[]'}} }" class="w-full">
                <label for="ckforce" class="flex items-center mb-5">
                    <input id="ckforce" type="checkbox" checked="checked" name="force" x-model="force" class="checkbox checkbox-primary" x-on:checked="force = true" x-effect="if(!force) rooms = {{!empty($roomOld) ? '[' . implode(',', array_keys($roomOld)) .']' : '[]'}}" />
                    <span class="font-bold ml-3">Force Assign</span> 
                </label>
                <div class="flex flex-wrap flex-auto justify-center justify-self-stretch gap-5 w-full">
                    @forelse ($rooms as $key => $item)
                        @php
                            $roomsPax = $item->getAllPax();
                            $roomVacant = $item->getVacantPax();
                        @endphp
                        <div :id="!force ? '' : '{{in_array($item->id, $reserved ?? []) ? 'disabledAll' : ''}}' " x-data="{reserved{{$loop->index+1}}: {{in_array($item->id, $reserved ?? []) ? 'true' : 'false'}} }">
                            <input x-ref="RoomRef" x-effect="rooms = rooms.map(function (x) { return parseInt(x, 10); }); " type="checkbox" x-model="rooms" value="{{$item->id}}" id="RoomNo{{$item->room_no}}" class="peer hidden [&:checked_+_label_span]:h-full [&:checked_+_label_span_h4]:block [&:checked_+_label_span_div]:block" x-on:checked="rooms.includes({{$item->id}})" :disabled="!force && reserved{{$loop->index+1}}"  />
                            <label for="RoomNo{{$item->room_no}}">
                                <div :class="!force ? '{{in_array($item->id, $reserved ?? []) ? 'border-red-600' : 'border-primary'}}' : 'border-primary' " class="relative w-52 overflow-hidden rounded-lg border p-4 sm:p-6 lg:p-8 cursor-pointer">
                                    <span class="absolute inset-x-0 bottom-0 flex flex-col items-center justify-center" :class="!force ? '{{in_array($item->id, $reserved ?? []) ? 'bg-red-600 h-full' : 'bg-primary h-3'}}' : 'bg-primary h-3'">
                                        <h3 :class="!force ? '{{in_array($item->id, $reserved ?? []) ? 'block' : 'hidden'}}' : 'hidden' " class="text-base-100 block font-medium w-full text-center">Full</h3>
                                        <h4 class="text-primary-content hidden font-medium w-full text-center" >Room No. {{$item->room_no}} Selected</h4> 
                                        <div x-data="{count: {{isset($roomInfo['rm'][$item->id]) ? (int)$roomInfo['rm'][$item->id] : 1}}}" class="join hidden">
                                            <button  @click="count > 1 ? count-- : count = 1" type="button" class="btn btn-accent btn-xs join-item rounded-l-full">-</button>
                                            <input x-model="count" type="number" :name="rooms.includes({{$item->id}}) ? 'room_pax[{{$item->id}}]' : '' " class="input input-bordered w-10 input-xs input-accent join-item" min="1" :value="!force ? '' : '{{$item->room->max_occupancy}}'" :max="!force ? '' : '{{$item->room->max_occupancy}}'" readonly/>
                                            <button  @click="count < {{$item->room->max_occupancy}} || force  ? count++ : count = {{$item->room->max_occupancy}}"  type="button" class="btn btn-accent btn-xs last:-item rounded-r-full">+</button>
                                        </div>
                                    </span>
                                    <div class="sm:flex sm:justify-between sm:gap-4">
                                        <div>
                                            <h3 class="text-lg font-bold text-neutral sm:text-xl">Room No. {{$item->room_no}}</h3>
                                            <p class="mt-1 text-xs font-medium text-gray-600">{{$item->room->name}}</p>
                                            <p class="mt-1 text-xs font-medium text-gray-600">{{$item->room->max_occupancy}} capacity</p>
                                            @if($roomsPax === (int)$item->room->max_occupancy - 1)
                                                <p x-show="!force" class="mt-1 text-xs font-bold text-error">There is only {{$roomVacant}} guest</p>
                                            @else
                                                <p x-show="!force" class="mt-1 text-sm font-medium ">{{ $roomsPax > 0 ?  $roomsPax . ' guest availed' : 'No guest'}} </p>
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
            </div>
            <div class="flex justify-end space-x-2 mt-5">
                <a href="{{route('system.reservation.create')}}" class="btn btn-ghost" @click="loader = true">Back</a>
                <button class="btn btn-primary" @click="loader = true">Next</button>
            </div>
        </form>
    </section>
  </x-system-content>
  @push('scripts')
        <script type="module" src="{{Vite::asset('resources/js/flatpickr2.js')}}"></script>
  @endpush
</x-system-layout>