@php
    $total = 0;
    $downpayment = 0;
    $dscPerson = null;

    foreach ($r_list->transaction as $outerKey => $outerItem) {
        if ($outerKey == 'payment' && is_array($outerItem)) {
            $downpayment = $outerItem['downpayment'] ?? 0;
            $dscPerson = $outerItem['discountPerson'] ?? 0;
            continue;
        }
    }
    $roomsNo = [];
    foreach ($rooms as $value) {
        if(isset($value->customer[$r_list->id])) $roomsNo[] = 'Room No. ' . $value->room_no;
    }
    $roomNo = implode(',', $roomsNo);

@endphp

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
            <article class="text-md tracking-tight text-neutral my-5 w-auto">
                <h2 class="text-2xl mb-5 font-bold">Details</h2>
                <p class="my-1"><strong>Number of Guest: </strong>{{$r_list->pax . ' guest' ?? 'None'}}</p>
                @if(!empty($r_list->tour_pax))
                    <p class="my-1"><strong>Guest going on tour: </strong>{{$r_list->tour_pax . ' guest' ?? 'None'}}</p>
                @endif
                <p class="my-1"><strong>Type: </strong>{{$r_list->accommodation_type ?? 'None'}}</p>
                <p class="my-1"><strong>Room No: </strong>{{$roomNo ?? 'None'}}</p>
                <p class="my-1"><strong>Check-in: </strong>{{ \Carbon\Carbon::createFromFormat('Y-m-d', $r_list->check_in )->format('l, F j, Y') ?? 'None'}}</p>
                <p class="my-1"><strong>Check-out: </strong>{{ \Carbon\Carbon::createFromFormat('Y-m-d', $r_list->check_out )->format('l, F j, Y') ?? 'None'}}</p>
                <p class="my-1"><strong>Payment Method: </strong>{{ $r_list->payment_method ?? 'None'}}</p>
                <p class="my-1"><strong>Status: </strong>{{ $r_list->status() ?? 'None'}}</p>
            </article>
            <article>
                <h2 class="text-2xl mb-5 font-bold">Reschedule Request<sup class="text-sm text-error">{{$r_list->status === 5 ? ' *Reservation Canceled' : ''}}</sup></h2>
                <div class="grid grid-flow-row md:grid-flow-col">
                    <div>
                        <h2 class="text-lg font-bold">Message</h2>
                        <p class="text-md">{{$r_list->message['reschedule']['message'] ?? 'None'}}</p>
                        <div class="my-5">
                            <span class="text-lg font-bold">Check-in Request:</span>
                            <span class="text-md">{{($r_list->message['reschedule']['check_in'] ?? false) ? \Carbon\Carbon::createFromFormat('Y-m-d', $r_list->message['reschedule']['check_in'])->format('l, F j, Y') : 'None'}}</span><br>
                            <span class="text-lg font-bold">Check-out Request:</span>
                            <span class="text-md">{{($r_list->message['reschedule']['check_out'] ?? false)? \Carbon\Carbon::createFromFormat('Y-m-d', $r_list->message['reschedule']['check_out'])->format('l, F j, Y') : 'None'}}</span><br>
                        </div>
                    </div>
                </div>
                @if($r_list->message['reschedule']['check_in'] ?? false )
                    <article class="text-md tracking-tight text-neutral my-5 w-auto">
                        <h2 class="text-2xl mb-5 font-bold">Who Availed on {{$r_list->message['reschedule']['check_in'] ? \Carbon\Carbon::createFromFormat('Y-m-d', $r_list->message['reschedule']['check_in'])->format('l, F j, Y') : 'None'}}</h2>
                        <div class="overflow-x-auto w-full">
                            <table class="table w-full">
                                <!-- head -->
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Name</th>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <!-- row 1 -->
                                    @forelse ($availed as $list)
                                        <tr>
                                            <td>
                                                <div class="flex items-center space-x-3">
                                                    <div class="avatar">
                                                        <div class="mask mask-squircle w-12 h-12">
                                                            <img src="{{$list->userReservation->avatar ? asset('storage/'.$list->userReservation->avatar) : asset('images/avatars/no-avatar.png')}}" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                            <div>
                                                <div class="font-bold">{{$list->userReservation->name() ?? ''}}</div>
                                                <div class="text-sm opacity-50">{{$list->userReservation->country}}</div>
                                            </div>
                                            </td>
                                            <td class="font-bold">{{ \Carbon\Carbon::parse($list->check_in)->format('F j, Y')}}</td>
                                            <td class="font-bold">{{ \Carbon\Carbon::parse($list->check_out)->format('F j, Y')}}</td>
                                            <td>{{$list->status()}}</td>
                                            <td>{{ \Carbon\Carbon::parse($list->created_at)->format('M j, Y g:i A')}}</td>
        
                                            <th class="w-auto">
                                                <a href="{{route('system.reservation.show', encrypt($list->id))}}" class="btn btn-info btn-xs" >View</a>
                                            </th>
                                        
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center font-bold">No Availed</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
        
                        </div>
                    </article>
                @endif
            </article>

        </div>
        <div class="divider"></div>
        <div class="flex justify-end space-x-2">
            <label for="approve_modal" class="btn btn-sm btn-primary" {{$r_list->status === 5 || !isset($r_list->message['reschedule']) ? 'disabled' : ''}}>Approve</label>
            <form id="approve-form" action="{{route('system.reservation.reschedule.update', encrypt($r_list->id))}}" method="post">
                <x-modal title="Before Approve: Change Room Assign" id="approve_modal">
                        @csrf
                        @method('PUT')
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
                        <div class="modal-action">
                            <label for="approve" class="btn btn-primary">Save</label>
                        </div>
                    
                </x-modal>
                <x-passcode-modal title="Approve Confirmation" id="approve" formId="approve-form" />        
            </form>
            <label for="disaprove_modal" class="btn btn-sm btn-error" {{$r_list->status === 5 || !isset($r_list->message['reschedule']) ? 'disabled' : ''}}>Dissaprove</label>
            <x-modal id="disaprove_modal" title="Why Disaprove Reschedule">
                <form x-data="{resched: ''}" action="{{route('system.reservation.update.reschedule.disaprove', encrypt($r_list->id))}}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="flex flex-col gap-2 my-3">
                        <label class="space-x-2" for="reason1">
                            <input type="radio" x-model="resched" class="radio radio-primary" name="reason" id="reason1" value="No Room Available">
                            <span>No Room Available</span>
                        </label>
                        <label class="space-x-2" for="reason2">
                            <input type="radio" x-model="resched" class="radio radio-primary" name="reason" id="reason2" value="No Room Available">
                            <span>Invalid Reason</span>
                        </label>
                        <label class="space-x-2" for="reason3">
                            <input type="radio" x-model="resched" class="radio radio-primary" id="reason3" value="Other">
                            <span>Other</span>
                        </label>
                    </div>
                    <template x-if="resched === 'Other' ">
                        <x-textarea placeholder="Reason Message" name="reason" id="reason" />
                    </template>
                    <div class="modal-action">
                        <button class="btn btn-sm btn-error">Proceed Disaprove</button>
                    </div>
                </form>
            </x-modal>
        </div>
       </div>
    </x-system-content>
</x-system-layout>