<x-system-layout :activeSb="$activeSb">
    <x-system-content title="">
        {{-- User Details --}}
        <div class="w-full">
            <div class="flex justify-between">
                <h2 class="text-2xl font-semibold">Choose the room for {{$r_list->userReservation->first_name}} {{$r_list->userReservation->last_name}}</h2>
                <div class="my-2 space-y-3">
                    <div class="dropdown dropdown-left">
                        <label tabindex="0" class="btn btn-ghost"><i class="fa-solid fa-circle-info"></i></label>
                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                            <li>                    
                                <div class="flex items-center space-x-2">
                                    <label class="h-8 w-8 rounded-full bg-red-600 shadow-sm" ></label>
                                    <p class="font-medium">Reserved</p>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center space-x-2">
                                    <label class="h-8 w-8 rounded-full bg-primary shadow-sm" ></label>
                                    <p class="font-medium">Available</p>
                                </div>
                            </li>
                        </ul>
                      </div>
                </div>
            </div>
            <form id="reservation-form" action="{{route('system.reservation.show.rooms.update', encrypt($r_list->id))}}" method="post">
                @csrf
                @method('PUT')
                <div class="form-control w-full">
                    <label for="room_rate" class="w-full relative flex justify-start rounded-md border border-base-200 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary ">
                            <select name="room_rate" id="room_rate" class='w-full select select-primary peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0'>
                            <option value="" disabled selected>Please select</option>
                            @foreach ($rates as $key => $rate)
                                @if(old('room_rate') == $rate->id);
                                    <option value="{{$rate->id}}" selected>{{$rate->name}} ({{$rate->occupancy}} pax)</option>
                                @elseif($rate->occupancy == $r_list->pax)
                                    <option value="{{$rate->id}}" selected>{{$rate->name}} ({{$rate->occupancy}} pax)</option>
                                @else
                                    <option value="{{$rate->id}}">{{$rate->name}} ({{$rate->occupancy}} pax)</option>
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
                <div class="flex flex-wrap justify-center md:justify-normal flex-grow m-5 gap-5 w-full">
                    @forelse ($rooms as $key => $item)
                    <div id="{{$item->availability == 1 ? 'disabledAll' : 'none'}}">
                        @if($item->availability == 1)
                            <input type="checkbox" name="rooms[]" value="{{$item->id}}" id="{{$item->room_no}}" class="peer hidden [&:checked_+_label_span]:h-full [&:checked_+_label_span_h4]:block" disabled/>
                        @elseif($errors->has('rooms') && Arr::exists(old('rooms'), $item->id))
                            <input type="checkbox" name="rooms[]" value="{{$item->id}}" id="{{$item->room_no}}" class="peer hidden [&:checked_+_label_span]:h-full [&:checked_+_label_span_h4]:block" checked/>
                        @else
                            <input type="checkbox" name="rooms[]" value="{{$item->id}}" id="{{$item->room_no}}" class="peer hidden [&:checked_+_label_span]:h-full [&:checked_+_label_span_h4]:block" />
                        @endif
                        <label for="{{$item->room_no}}">
                            <div class="relative w-52 overflow-hidden rounded-lg border p-4 sm:p-6 lg:p-8 {{$item->availability == 1 ? 'opacity-70 bg-red-600' : 'border-primary cursor-pointer'}}">
                                @if($item->availability == 1)
                                    <span class="absolute inset-x-0 bottom-0 h-full  bg-red-500 opacity-80 flex items-center"><h4 class="text-base-100 block font-medium w-full text-center">Reserved</h4></span>
                                @else
                                    <span class="absolute inset-x-0 bottom-0 h-3 bg-primary flex items-center"><h4 class="text-primary-content hidden font-medium w-full text-center">Room No. {{$item->room_no}} Selected</h4></span>
                                @endif
                                <div class="sm:flex sm:justify-between sm:gap-4">
                                    <div>
                                        <h3 class="text-lg font-bold text-neutral sm:text-xl">Room No. {{$item->room_no}}</h3>
                                        <p class="mt-1 text-xs font-medium text-gray-600">{{$item->room->name}}</p>
                                        <p class="mt-1 text-xs font-medium text-gray-600">{{$item->room->min_occupancy}} up to {{$item->room->max_occupancy}} capacity</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                    @empty
                        <p class="text-2xl font-semibold">No Record Found</p>
                    @endforelse
                </div>
                <x-passcode-modal title="Enter the correct passcode to approve for {{$r_list->userReservation->first_name}} {{$r_list->userReservation->last_name}}" id="reservation" formId="reservation-form" />

            </form>
        </div>
        <div class="flex justify-end space-x-1">
            <label for="reservation" class="btn btn-secondary btn-sm" >Approve</label>
            <a href="{{route('system.reservation.disaprove', encrypt($r_list->id))}}" class="btn btn-error btn-sm" >Disapprove</a>
        </div>
    </x-system-content>
</x-system-layout>
