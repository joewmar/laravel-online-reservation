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
       <div class="px-3 md:px-20">
        <x-profile :rlist="$r_list" />
        <div class="my-5 flex justify-between items-center">
            <h2 class="text-xl md:text-2xl font-bold">Reschedule Request<sup class="text-sm text-primary">{{$r_list->status === 5 ? ' *Approved' : ''}}</sup></h2>
            <label for="frsch_mdl" class="btn btn-error btn-sm" {{$r_list->status < 1 ? 'disabled' : ''}}>Force Reschedule</label>
            @if(!($r_list->status < 1))
                <x-modal title="Before Proceed, Choose date to force reschedule" id="frsch_mdl">
                    <form action="{{route('system.reservation.force.reschedule', encrypt($r_list->id))}}" method="GET">
                        @csrf
                        <p><span class="font-bold">Previous Check-in:</span> {{Carbon\Carbon::createFromFormat('Y-m-d', $r_list->check_in)->format('F j, Y')}}</p>
                        <p><span class="font-bold">Previous Check-out:</span> {{Carbon\Carbon::createFromFormat('Y-m-d', $r_list->check_out)->format('F j, Y')}}</p>
                        <div class="mt-5">
                            <x-datetime-picker name="check_in" id="check_in" placeholder="Check in" class="flatpickr-reservation" />
                            <x-datetime-picker name="check_out" id="check_out" placeholder="Check out" class="flatpickr-reservation" />
                        </div>
                        <div class="modal-action">
                            <button class="btn btn-primary">Proceed</button>
                        </div>
                    </form>
                </x-modal>
            @endif
        </div>
        <div class="divider"></div>
        <div class="w-full">
            {{-- <article class="text-md tracking-tight text-neutral my-5 w-auto">
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
            </article> --}}
            <article>
                <div class="grid grid-flow-row md:grid-flow-col">
                    @if(!($r_list->status === 5 || !isset($r_list->message['reschedule'])))
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
                    @else
                        <div>
                            <h2 class="text-lg font-medium text-center">No Request</h2>
                        </div>
                    @endif
                </div>
                @if($r_list->message['reschedule']['check_in'] ?? false )
                    <article class="text-md tracking-tight text-neutral my-5 w-auto">
                        <h2 class="text-xl md:text-2xl mb-5 font-bold">Who Availed on {{$r_list->message['reschedule']['check_in'] ? \Carbon\Carbon::createFromFormat('Y-m-d', $r_list->message['reschedule']['check_in'])->format('l, F j, Y') : 'None'}}</h2>
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
            @if(!($r_list->status === 5 || !isset($r_list->message['reschedule'])))
                <x-modal title="Before Approve: Change Room Assign" id="approve_modal" width noBottom>
                    <form id="approve-form" action="{{route('system.reservation.reschedule.update', encrypt($r_list->id))}}" method="post">
                        @csrf
                        @method('PUT')
                        <x-rooms id="infomdl" :rooms="$rooms" :rlist="$r_list" :reserved="$reserved" />

                        <div class="modal-action">
                            <button class="btn btn-primary">Save</button>
                        </div>
                            
                            
                    </form>                    
                
                </x-modal>
            @endif

            <label for="disaprove_modal" class="btn btn-sm btn-error" {{$r_list->status === 5 || !isset($r_list->message['reschedule']) ? 'disabled' : ''}}>Dissaprove</label>
            @if(!($r_list->status === 5 || !isset($r_list->message['reschedule'])))
                <x-modal id="disaprove_modal" title="Why Disapprove Reschedule">
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
                            <button class="btn btn-sm btn-error">Proceed Disapprove</button>
                        </div>
                    </form>
                </x-modal>
            @endif
        </div>
       </div>
    </x-system-content>
    @if(!($r_list->status < 1))
        @push('scripts')
            <script>
                @if(isset($operation->from) && isset($operation->to))
                const mop = {
                    from: '{{$operation->from}}',
                    to: '{{$operation->to}}'
                };
                @else
                const mop = '2001-15-30';
                @endif
                const md = '{{Carbon\Carbon::now()->addDays(2)->format('Y-m-d')}}';
                const mrsh = {
                    from: '{{$r_list->check_in}}',
                    to: '{{$r_list->check_out}}'
                };
            </script>
            <script type="module" src="{{Vite::asset('resources/js/flatpickr.js')}}"></script>
        @endpush
    @endif
</x-system-layout>