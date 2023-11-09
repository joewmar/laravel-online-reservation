<x-system-layout :activeSb="$activeSb">
    <x-system-content title="" back="{{route('system.reservation.show', encrypt($r_list->id))}}">
        {{-- User Details --}}
       <div class="px-3 md:px-20">

        <x-profile :rlist="$r_list" />
        <div class="divider"></div>
        <div class="my-5 flex justify-between items-center">
            <h2 class="text-xl md:text-2xl  font-bold">Cancellation Request<sup class="text-sm text-error">{{$r_list->status === 5 ? ' *Reservation Canceled' : ''}}</sup></h2>
            <a href="{{route('system.reservation.force.cancel', encrypt($r_list->id))}}" class="btn btn-error btn-sm" {{$r_list->status > 0 ? '' : 'disabled'}}>Force Cancel</a>
        </div>
        <div class="divider"></div>
        <div class="w-full">
            <div class="grid grid-flow-row md:grid-flow-col">
                @if(!($r_list->status === 4 || $r_list->status === 5 || !isset($r_list->message['cancel'])))
                    <div>
                        <h2 class="text-lg font-bold">Message</h2>
                        <p class="text-md">{{$r_list->message['cancel']['message'] ?? 'None'}}</p>
                    </div>
                @else
                    <div>
                        <h2 class="text-lg font-medium text-center">No Request</h2>
                    </div>
                @endif

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
            <x-modal id="disaprove_modal" title="Why Disapprove Cancel">
                <form action="{{route('system.reservation.update.cancel.disaprove', encrypt($r_list->id))}}" method="POST">
                    @csrf
                    @method('PUT')
                    <x-textarea placeholder="Reason Message" name="reason" id="reason" />
                    <div class="modal-action">
                        <button class="btn btn-sm btn-error">Proceed Disapprove</button>
                    </div>
                </form>
            </x-modal>
        </div>
       </div>
    </x-system-content>
</x-system-layout>