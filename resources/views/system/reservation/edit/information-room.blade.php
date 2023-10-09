@php
    $status = ['Pending', 'Confirmed', 'Check-in', 'Check-out', 'Cancel'];;   
@endphp

<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Edit Reservation Information of {{$r_list->userReservation->name()}}" back=true>
            <form id="infomdlform" method="POST" action="{{route('system.reservation.edit.information.room.update', ['id' => encrypt($r_list->id), Arr::query(encryptedArray($info))])}}" class="px-8 my-5">
                @csrf
                @method('PUT')
                <section class="w-full">
                    <div class="text-lg font-bold mb-5">Before Save, Required to @if($r_list->pax !== $info['px']) Change Room Assign @endif @if($info['st'] == 1 || $info['st'] == 2) @if($r_list->pax !== $info['px']) and @endif Update Payment @endif </div>
                    <div class="text-sm font-medium">New Guest: {{ $info['px'] }} pax</div>
                    <div class="text-sm font-medium mb-5">New Status: {{ $status[$info['st']] }}</div>
                    <div class="text-sm font-medium">Check-in: {{Carbon\Carbon::createFromFormat('Y-m-d', $info['cin'])->format('(l) F j, Y')}}</div>
                    <div class="text-sm font-medium mb-5">Check-out: {{Carbon\Carbon::createFromFormat('Y-m-d', $info['cout'])->format('(l) F j, Y')}}</div>
                    @if($r_list->status != $info['st'] && $info['st'] == 1)
                        <x-input name="amountdy" id="amountdy" placeholder="Downpayment" value="{{$r_list->downpayment() > 0 ? $r_list->downpayment() : 1000}}" min="1000" max="{{$r_list->balance()}}" /> 
                    @endif
                    @if($r_list->status != $info['st'] && $info['st'] == 2)
                        <article x-data="{pay: '{{isset($r_list->transaction['payment']['cinpay']) && $r_list->transaction['payment']['cinpay'] === 0 ? 'fullpayment' : 'partial'}}', senior: {{isset($r_list->transaction['payment']['discountPerson']) ? 'true' : 'false'}} }">
                                <div class="mb-5">
                                    <input id="discount" x-model="senior" name="hs" type="checkbox" class="checkbox checkbox-secondary" />
                                    <label for="discount" class="ml-4 font-semibold">Check-in: Have Senior Citizen?</label>
                                </div>
                                <template x-if="senior">
                                    <div class="mt-3">
                                        <x-input type="number" name="senior_count" id="senior_count" placeholder="Count of Senior Guest" value="{{$r_list->transaction['payment']['discountPerson'] ?? 1}}" />
                                    </div>
                                </template>
                                <div class="py-3 space-x-2">
                                    <input type="radio" x-model="pay" id="partial" name="cnpy" class="radio radio-primary" value="partial" />
                                    <label for="partial">Partial</label>
                                    <input type="radio" x-model="pay" id="full_payment" name="cnpy" class="radio radio-primary" value="fullpayment" />
                                    <label for="full_payment">Full Payment</label>
                                    <template x-if="pay == 'partial'">
                                        <div class="mt-3">
                                            <x-input name="amountcinp" id="amountcinp" placeholder="Amount" value="{{$r_list->checkInPayment() ?? ''}}" max="{{$r_list->balance()}}" /> 
                                        </div>
                                    </template>
                                </div>
                        </article>
                    @endif
                    @if($r_list->pax != $info['px'] || $info['st'] == 1 || $info['st'] == 2) 
                        <div class="divider"></div>

                        <x-rooms id="infomdl" :rooms="$rooms" haveRate :rates="$rates" :rlist="$r_list" :reserved="$reserved" includeID />

                        <div class="flex justify-end space-x-1">
                            <x-passcode-modal title="Enter the correct passcode to approve for {{$r_list->userReservation->name()}}" id="infomdl" formId="infomdlform" />

                            <label for="infomdl" class="btn btn-primary">Save</label>
                        </div>
                    @else
                        <x-passcode-modal title="Enter the correct passcode to approve for {{$r_list->userReservation->name()}}" id="infomdl" formId="infomdlform" />

                        <div class="flex justify-end space-x-1">
                            <label for="infomdl" class="btn btn-primary">Save</label>
                        </div>
                    @endif
                </section>
            </form>
        </div>

    </x-system-content>
</x-system-layout>