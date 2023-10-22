<x-system-layout :activeSb="$activeSb">
    <x-system-content title="" back="{{route('system.reservation.show', encrypt($r_list->id))}}">
        {{-- User Details --}}
        <div class="flex justify-between">
            <div>
                <h2 class="text-2xl font-semibold">Choose the room for {{$r_list->userReservation->name()}} ({{$r_list->pax}} guest)</h2>
                <div class="text-sm mt-5"><span class="font-medium">Check-in:</span> {{ \Carbon\Carbon::createFromFormat('Y-m-d', $r_list->check_in )->format('F j, Y') ?? 'None'}}</div>
                <div class="text-sm mb-5"><span class="font-medium">Check-out:</span> {{ \Carbon\Carbon::createFromFormat('Y-m-d', $r_list->check_out )->format('F j, Y') ?? 'None'}}</div>
            </div>
            <div class="space-y-3">
                <div class="dropdown dropdown-left">
                    <label tabindex="0" class="btn btn-ghost"><i class="fa-solid fa-circle-info"></i></label>
                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                        <li>                    
                            <div class="flex items-center space-x-2">
                                <label class="h-8 w-8 rounded-full bg-red-600 shadow-sm" ></label>
                                <p class="font-medium">Full</p>
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
        <form id="reservationform" action="{{route('system.reservation.show.rooms.update', encrypt($r_list->id))}}" method="post">
            @csrf
            @method('PUT')
            <x-rooms id="reservation" :rooms="$rooms" haveRate :rates="$rates" :rlist="$r_list" :reserved="$reserved" />
            <x-passcode-modal title="Enter the correct passcode to approve for {{$r_list->userReservation->name()}}" id="reservation" formId="reservationform" />

            <div class="mt-5 flex justify-end join">
                <label for="reservation" class="btn btn-secondary btn-sm join-item" >Approve</label>
                <a href="{{route('system.reservation.disaprove', encrypt($r_list->id))}}" class="btn btn-error btn-sm join-item" >Disapprove</a>
            </div>
        </form>
    </x-system-content>
</x-system-layout>
