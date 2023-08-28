
<x-system-layout :activeSb="$activeSb">
    <x-system-content title="" back=true>
        {{-- User Details --}}
       <div class="px-0 md:px-20">
        <div class="w-full sm:flex sm:space-x-6">
            <div class="flex-shrink-0 mb-6 h-15 sm:h-32 w-15 sm:w-32 sm:mb-0">
                @if(filter_var($r_list->userReservation->avatar ?? '', FILTER_VALIDATE_URL))
                    <img src="{{$r_list->userReservation->avatar}}" alt="" class="object-cover object-center w-full h-full rounded">
                @elseif($r_list->userReservation->avatar ?? false)
                    <img src="{{asset('storage/'. $r_list->userReservation->avatar)}}" alt="" class="object-cover object-center w-full h-full rounded">
                @else
                    <img src="{{asset('images/avatars/no-avatar.png')}}" alt="" class="object-cover object-center w-full h-full rounded">
                @endif
            </div>            
            <div class="flex flex-col space-y-4">
                <div>
                    <h2 class="text-2xl font-semibold">{{$r_list->userReservation->name()}}</h2>
                    <span class="block text-sm text-neutral">{{$r_list->userReservation->age()}} years old from {{$r_list->userReservation->country}}</span>
                    <span class="text-sm text-neutral">{{$r_list->userReservation->nationality}}</span>
                </div>
                <div class="space-y-1">
                    <span class="flex items-center space-x-2">
                        <i class="fa-regular fa-envelope w-4 h-4"></i>
                        <span class="text-neutral">{{$r_list->userReservation->email}}</span>
                    </span>
                    <span class="flex items-center space-x-2">
                        <i class="fa-solid fa-phone w-4 h-4"></i>
                        <span class="text-neutral">{{$r_list->userReservation->contact}}</span>
                    </span>
                </div>
            </div>
        </div>
        <div class="divider"></div>
        <div class="w-full">
            <div class="grid grid-flow-row md:grid-flow-col">
                <div>
                    <div class="my-5">
                        <span class="text-lg font-bold">Check-in Request:</span>
                        <span class="text-md">{{($r_list->message['reschedule']['check_in'] ?? false) ? \Carbon\Carbon::createFromFormat('Y-m-d', $r_list->message['reschedule']['check_in'])->format('l, F j, Y') : 'None'}}</span><br>
                        <span class="text-lg font-bold">Check-out Request:</span>
                        <span class="text-md">{{($r_list->message['reschedule']['check_out'] ?? false)? \Carbon\Carbon::createFromFormat('Y-m-d', $r_list->message['reschedule']['check_out'])->format('l, F j, Y') : 'None'}}</span><br>
                    </div>
                </div>
            </div>
            <h2 class="text-2xl mb-5 font-bold">Before Approve: Change Room Assign</h2>
            <div x-data="{rooms: []}" class="flex flex-wrap justify-center md:justify-normal flex-grow gap-5 w-full">
                @forelse ($rooms as $key => $item)
                    <div>
                        <input x-ref="RoomRef" x-effect="rooms = rooms.map(function (x) { return parseInt(x, 10); });" type="checkbox" x-model="rooms" value="{{$item->id}}" id="RoomNo{{$item->room_no}}" class="peer hidden [&:checked_+_label_span]:h-full [&:checked_+_label_span_h4]:block [&:checked_+_label_span_div]:block" x-on:checked="rooms.includes({{$item->id}})" />
                        <label for="RoomNo{{$item->room_no}}">
                            <div class="relative w-52 overflow-hidden rounded-lg border p-4 sm:p-6 lg:p-8 border-primary cursor-pointer'">
                                <span class="absolute inset-x-0 bottom-0 h-3 bg-primary flex flex-col items-center justify-center">
                                    <h4 class="text-primary-content hidden font-medium w-full text-center">Room No. {{$item->room_no}} Selected</h4> 
                                        <div x-data="{count: {{isset(old('room_pax')[$item->id]) ? (int)old('room_pax')[$item->id] : 1}}}" class="join hidden">
                                        <button  @click="count > 1 ? count-- : count = 1" type="button" class="btn btn-accent btn-xs join-item rounded-l-full">-</button>
                                        <input x-model="count" type="number" :name="rooms.includes({{$item->id}}) ? 'room_pax[{{$item->id}}]' : '' " class="input input-bordered w-10 input-xs input-accent join-item" min="1" max="{{$item->room->max_occupancy}}"  readonly/>
                                        <button  @click="count < {{$item->room->max_occupancy}} ? count++ : count = {{$item->room->max_occupancy}}"  type="button" class="btn btn-accent btn-xs last:-item rounded-r-full">+</button>
                                    </div>
                                </span>
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
                    <p class="text-2xl font-semibold">No Room Found</p>
                @endforelse
            </div>

        </div>
        <div class="divider"></div>
        <div class="flex justify-end space-x-3">
            {{-- <label for="disaprove_modal" class="btn btn-sm btn-error" {{$r_list->status === 5 || !isset($r_list->message['reschedule']) ? 'disabled' : ''}}>Dissaprove</label> --}}
            <label class="btn btn-sm btn-secondary">Check</label>
            <label class="btn btn-sm btn-primary" disabled>Approve</label>
            <x-passcode-modal title="Enter the correct passcode to approve for {{$r_list->userReservation->name()}}" id="reservation" formId="reservation-form" />
        </div>
       </div>
    </x-system-content>
</x-system-layout>