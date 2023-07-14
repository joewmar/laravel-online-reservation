
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
                <div class="flex flex-wrap justify-center md:justify-normal flex-grow m-5 gap-5 w-full">
                    @forelse ($rooms as $key => $item)
                    <div id="{{$item->availability == 1 ? 'disabledAll' : 'none'}}">
                        @if($item->availability == 1)
                            <input type="checkbox" name="rooms[]" value="{{$item->id}}" id="{{$item->room_no}}" class="peer hidden [&:checked_+_label_span]:h-full [&:checked_+_label_span_h4]:block" disabled/>
                        @elseif($errors->has('rooms') && in_array($item->id, old('rooms')))
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
            <label for="reservation" class="btn btn-secondary btn-sm" >Confirm</label>
        </div>
    </x-system-content>
</x-system-layout>
