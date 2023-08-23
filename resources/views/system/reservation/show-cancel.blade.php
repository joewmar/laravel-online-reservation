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
            <h2 class="text-2xl mb-5 font-bold">Cancellation Request<sup class="text-sm text-error">{{$r_list->status === 5 ? ' *Reservation Canceled' : ''}}</sup></h2>
            <div class="grid grid-flow-row md:grid-flow-col">
                <div>
                    <h2 class="text-lg font-bold">Message</h2>
                    <p class="text-md">{{$r_list->message['cancel']['message'] ?? 'None'}}</p>
                </div>
            </div>
        </div>
        <div class="divider"></div>
        <div class="flex justify-end space-x-2">
            <label for="canceled_modal" class="btn btn-sm btn-primary" {{$r_list->status === 4 || $r_list->status === 5 || !isset($r_list->message['cancel'])  ? 'disabled' : ''}}>Approve</label>
            <form id="canceled-form" action="{{route('system.reservation.update.cancel', encrypt($r_list->id))}}" method="post">
                @csrf
                @method('PUT')
                <x-passcode-modal title="Cancellation Confirmation" id="canceled_modal" formId="canceled-form" loader />        
            </form>
            <label for="disaprove_modal" class="btn btn-sm btn-error" {{$r_list->status === 4 || $r_list->status === 5 || !isset($r_list->message['cancel']) ? 'disabled' : ''}}>Dissaprove</label>
            <x-modal id="disaprove_modal" title="Why Disaprove Cancel">
                <form action="{{route('system.reservation.update.cancel.disaprove', encrypt($r_list->id))}}" method="POST">
                    @csrf
                    @method('PUT')
                    <x-textarea placeholder="Reason Message" name="reason" id="reason" />
                    <div class="modal-action">
                        <button class="btn btn-sm btn-error">Proceed Disaprove</button>
                    </div>
                </form>
            </x-modal>
        </div>
       </div>
    </x-system-content>
</x-system-layout>