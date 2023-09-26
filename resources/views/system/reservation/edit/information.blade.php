@php
    $arrAccType = ['Room Only', 'Day Tour', 'Overnight'];
    $arrPayment = ['Walk-in', 'Other Booking', 'Gcash', 'PayPal'];
    $arrStatus = ['Pending', 'Confirmed', 'Check-in', 'Previous', 'Previous', 'Reshedule', 'Cancel'];
@endphp
<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Reservation Information of {{$r_list->userReservation->name()}}" back=true>
        <div x-data="{newpax: '{{old('pax') ?? $r_list->pax}}'}">
            <form :id="newpax === '{{$r_list->pax}}' ? '{{$r_list->roomid ? 'edit-passcode-info' : 'edit-info-form'}}' : '{{$r_list->roomid ? 'edit-info' : 'edit-info-form'}}' " method="POST" action="{{route('system.reservation.edit.information.update', encrypt($r_list->id))}}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <section class="w-full flex justify-center">
                    <div class="w-96 my-8">
                        <x-input type="number" name="age" id="age" placeholder="{{$r_list->userReservation->name()}} Age" value="{{old('age') ?? $r_list->age}}"/>
                        <x-datetime-picker name="check_in" id="check_in" placeholder="Check in" class="flatpickr-reservation-one" value="{{old('check_in') ?? $r_list->check_in}}"/>
                        <x-datetime-picker name="check_out" id="check_out" placeholder="Check out" class="flatpickr-reservation-one" value="{{old('check_out') ?? $r_list->check_out}}" />
                        <x-select name="accommodation_type" id="accommodation_type" placeholder="Accommodation Type" :value="$arrAccType" :title="$arrAccType" selected="{{old('accommodation_type') ?? $r_list->accommodation_type}}" />
                        <x-input type="number" name="pax" id="pax" placeholder="Number of Guests" xModel="newpax" />
                        <x-select id="payment_method" name="payment_method" placeholder="Payment Method" :value="$arrPayment"  :title="$arrPayment" selected="{{old('payment_method') ?? $r_list->payment_method}}"/>
                        @if($r_list->status >= 3)
                            <x-select id="status" name="status" placeholder="Status" :value="array_keys($arrStatus)"  :title="$arrStatus" selected="{{$arrStatus[old('status')] ?? $arrStatus[$r_list->status]}}" disabled=true />
                        @else
                            <x-select id="status" name="status" placeholder="Status" :value="array_keys($arrStatus)"  :title="$arrStatus" selected="{{$arrStatus[old('status')] ?? $arrStatus[$r_list->status]}}"  />
                        @endif
                        @if ($r_list->roomid)
                            <label :for="newpax === '{{$r_list->pax}}' ? 'info_passcode' : 'info_modal'" class="btn btn-primary btn-block">Save</label>

                            <div x-show="newpax === '{{$r_list->pax}}'">
                                <div>
                                    <x-passcode-modal title="Enter the correct passcode to save information for {{$r_list->userReservation->name()}}" id="info_passcode" formId="edit-passcode-info" />
                                </div>
                            </div>
                            <div x-show="!(newpax === '{{$r_list->pax}}')">
                                <div>
                                    <x-modal id="info_modal" title="Change Room Assign and Rate" loader width>
                                        <div class="text-sm font-medium mb-5">Change Guest: <span x-text="newpax"></span> pax</div>
                                        <div x-data="{force: false, rooms: {{$r_list->roomid ? '[' . implode(',', $r_list->roomid) .']' : '[]'}} }" class="w-full">
                                            <form id="reservation-form" action="{{route('system.reservation.show.rooms.update', encrypt($r_list->id))}}" method="post">
                                                @csrf
                                                @method('PUT')
                                                <label for="ckforce" class="flex items-center">
                                                    <input id="ckforce" type="checkbox" checked="checked" name="force" x-model="force" class="checkbox checkbox-primary" x-on:checked="force = true" x-effect="if(!force) rooms = {{$r_list->roomid ? '[' . implode(',', $r_list->roomid) .']' : '[]'}}" />
                                                    <span class="font-bold ml-3">Force Assign</span> 
                                                </label>
                                                <div class="form-control w-full mt-5">
                                                    <label for="room_rate" class="w-full relative flex justify-start rounded-md border border-gray-400 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary ">
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
                                                                @if(old('room_rate') == $rateID || $r_list->pax === $rate->occupancy || $r_list->pax > $rate->occupancy);
                                                                    <option value="{{encrypt($rate->id)}}" :selected="'{{$rate->occupancy}}'<= newpax">{{$rate->name}} ({{$rate->occupancy}} pax)</option>
                                                                @else
                                                                    <option value="{{encrypt($rate->id)}}" :selected="'{{$rate->occupancy}}' <= newpax">{{$rate->name}} ({{$rate->occupancy}} pax)</option>
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
                                                <div class="flex flex-wrap flex-auto justify-center justify-self-stretch gap-5 w-full">
                                                    @forelse ($rooms as $key => $item)
                                                        <div :id="!force ? '' : '{{in_array($item->id, $reserved) ? 'disabledAll' : ''}}' " x-data="{reserved{{$loop->index+1}}: {{in_array($item->id, $reserved) ? 'true' : 'false'}} }">
                                                            <input x-ref="RoomRef" x-effect="rooms = rooms.map(function (x) { return parseInt(x, 10); }); " type="checkbox" x-model="rooms" value="{{$item->id}}" id="RoomNo{{$item->room_no}}" class="peer hidden [&:checked_+_label_span]:h-full [&:checked_+_label_span_h4]:block [&:checked_+_label_span_div]:block" :checked="rooms.includes({{$item->id}})" :disabled="!force && reserved{{$loop->index+1}}"  />
                                                            <label for="RoomNo{{$item->room_no}}">
                                                                <div :class="!force ? '{{in_array($item->id, $reserved) ? 'border-red-600' : 'border-primary'}}' : 'border-primary' " class="relative w-52 overflow-hidden rounded-lg border p-4 sm:p-6 lg:p-8 cursor-pointer">
                                                                    <span class="absolute inset-x-0 bottom-0 flex flex-col items-center justify-center" :class="!force ? '{{in_array($item->id, $reserved) ? 'bg-red-600 h-full' : 'bg-primary h-3'}}' : 'bg-primary h-3'">
                                                                        <h3 :class="!force ? '{{in_array($item->id, $reserved) ? 'block' : 'hidden'}}' : 'hidden' " class="text-base-100 block font-medium w-full text-center">Full</h3>
                                                                        <h4 class="text-primary-content hidden font-medium w-full text-center" >Room No. {{$item->room_no}} Selected</h4> 
                                                                        <div x-data="{count: {{in_array($item->id, $r_list->roomid) ? (int)$item->customer[$r_list->id] ?? 1 : 1}}}" class="join hidden">
                                                                            <button  @click="count > 1 ? count-- : count = 1" type="button" class="btn btn-accent btn-xs join-item rounded-l-full">-</button>
                                                                            <input x-model="count" type="number" :name="rooms.includes({{$item->id}}) ? 'room_pax[{{$item->id}}]' : '' " class="input input-bordered w-10 input-xs input-accent join-item" min="1" max="{{$item->room->max_occupancy}}"  readonly/>
                                                                            <button  @click="count < {{$item->room->max_occupancy}} || force  ? count++ : count = {{$item->room->max_occupancy}}"  type="button" class="btn btn-accent btn-xs last:-item rounded-r-full">+</button>
                                                                        </div>
                                                                    </span>
                                                                    <div class="sm:flex sm:justify-between sm:gap-4">
                                                                        <div>
                                                                            <h3 class="text-lg font-bold text-neutral sm:text-xl">Room No. {{$item->room_no}}</h3>
                                                                            <p class="mt-1 text-xs font-medium text-gray-600">{{$item->room->name}}</p>
                                                                            <p class="mt-1 text-xs font-medium text-gray-600">{{$item->room->max_occupancy}} capacity</p>
                                                                            @if($item->getAllPax() === (int)$item->room->max_occupancy - 1)
                                                                                <p class="mt-1 text-xs font-bold text-error">There is only {{$item->getAllPax()}} guest</p>
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
                                                <x-passcode-modal title="Enter the correct passcode to approve for {{$r_list->userReservation->name()}}" id="reservation" formId="reservation-form" />
                                            </form>
                                        </div>
                                        <div class="flex justify-end space-x-1">
                                            <button @click="loader = true" class="btn btn-primary" >Save</button>
                                        </div>
                                    </x-modal>
                                </div>
                            </div>
                        @else
                            <label for="info_modal" class="btn btn-primary btn-block">Save</label>
                            <x-passcode-modal title="Enter the correct passcode to save information for {{$r_list->userReservation->name()}}" id="info_modal" formId="edit-info-form" />
                        @endif
                    </div>
                </section>
            </form>
        </div>

    </x-system-content>
</x-system-layout>