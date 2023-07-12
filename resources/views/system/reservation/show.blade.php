<x-system-layout :activeSb="$activeSb">
    <x-system-content title="">
        {{-- User Details --}}
        <div class="w-full p-8 sm:flex sm:space-x-6">
            <div class="flex-shrink-0 mb-6 h-15 sm:h-32 w-15 sm:w-32 sm:mb-0">
                <img src="{{asset('images/avatars/no-avatar.png')}}" alt="" class="object-cover object-center w-full h-full rounded">
            </div>
            <div class="flex flex-col space-y-4">
                <div>
                    <h2 class="text-2xl font-semibold">{{$r_list->userReservation->first_name}} {{$r_list->userReservation->last_name}}</h2>
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
        <div>
            <h2 class="text-2xl font-semibold">Details</h2>
        </div>
        <div class="flex justify-end space-x-1">
            @php
                $title = ''; 
            @endphp
            @if($r_list->status() == "Pending")
                @php $title = 'Approve' @endphp
                <label for="reservation" class="btn btn-secondary btn-xs" >Confirm</label>
                <label for="reservation" class="btn btn-success btn-xs" disabled>Check-in</label>
                <label for="reservation" class="btn btn-info btn-xs" disabled>Check-out</label>
            @elseif($r_list->status() == "Confirmed")
                @php $title = 'Check-in' @endphp
                <label for="reservation" class="btn btn-secondary btn-xs" disabled>Confirm</label>
                <label for="reservation" class="btn btn-success btn-xs">Check-in</label>
                <label for="reservation" class="btn btn-info btn-xs" disabled>Check-out</label>

            @elseif($r_list->status() == "Check-in")
                @php $title = 'Check-out' @endphp
                <label for="reservation" class="btn btn-secondary btn-xs" disabled>Confirm</label>
                <label for="reservation" class="btn btn-success btn-xs" disabled>Check-in</label>
                <label for="reservation" class="btn btn-info btn-xs">Check-out</label>
            @endif
            <form action="" method="post">
                @csrf
                <x-passcode-modal title="{{$title}} Confirmation" id="reservation" formId="reservation-form" />        
            </form>
        </div>
    </x-system-content>
</x-system-layout>
