@php
    $status = ['Pending', 'Confirmed', 'Check-in', 'Check-out', 'Cancel'];;   
@endphp

<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Edit Reservation Information of {{$r_list->userReservation->name()}}" back="{{route('system.reservation.edit.information', encrypt($r_list->id))}}">
            <form id="infomdlform" method="POST" action="{{route('system.reservation.edit.information.room.update', ['id' => encrypt($r_list->id), Arr::query(encryptedArray($info))])}}" class="px-8 my-5">
                @csrf
                @method('PUT')
                <section x-data="checkIn" class="w-full">
                    <div class="text-lg font-bold mb-5">Before Save, Required to @if($r_list->pax !== $info['px']) Change Room Assign @endif @if($info['st'] == 1 || $info['st'] == 2) @if($r_list->pax !== $info['px']) and @endif Update Payment @endif </div>
                    <div class="text-sm font-medium">New Guest: {{ $info['px'] }} pax</div>
                    <div class="text-sm font-medium mb-5">New Status: {{ $status[$info['st']] }}</div>
                    <div class="text-sm font-medium">Check-in: {{Carbon\Carbon::createFromFormat('Y-m-d', $info['cin'])->format('(l) F j, Y')}}</div>
                    <div class="text-sm font-medium mb-5">Check-out: {{Carbon\Carbon::createFromFormat('Y-m-d', $info['cout'])->format('(l) F j, Y')}}</div>
                    @if($r_list->status != $info['st'] && $info['st'] == 1)
                        <x-input name="amountdy" id="amountdy" placeholder="Downpayment" value="{{$r_list->downpayment() > 0 ? $r_list->downpayment() : 1000}}" min="1000" max="{{$r_list->balance()}}" /> 
                    @elseif($r_list->status != $info['st'] && $info['st'] == 2)
                        <div x-data="{pay: 0, senior: false, scount: 0, st: {{$r_list->getServiceTotal()}}, cintotal: {{$r_list->getTotal()}}, balance: {{$r_list->balance()}}, change: {{$r_list->refund()}}}">
                            <p class="text-sm"><strong>Services Total: </strong> <span x-text="st > 0 ? '₱ ' + st.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                            <p class="text-sm"><strong>Total Cost: </strong> <span x-text="cintotal > 0 ? '₱ ' + cintotal.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                            <p class="text-sm"><strong>Balance: </strong> <span x-text="balance > 0 ? '₱ ' + balance.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                            <div class="py-3">
                                <div class="mb-5 mt-3">
                                    <input id="discount" @change="CIN()" name="hs" x-model="senior" type="checkbox" class="checkbox checkbox-secondary checkbox-sm md:checkbox-md" />
                                    <label for="discount" class="text-sm md:text-lg ml-4 font-semibold">Have Senior Citizen?</label>
                                    <template x-if="senior">
                                        <div class="my-3">
                                            <x-input x-model="scount" type="number" name="senior_count" id="senior_count" placeholder="Count of Guest" value="{{old('senior_count')}}" input="CIN()" />
                                        </div>
                                    </template>
                                </div>
                                <x-input x-model="pay" type="number" name="amountcinp" id="amountcinp" placeholder="Amount" value="{{old('another_payment')}}" input="CIN()" />
                                <div x-show="change > 0" class="text-error" x-text="'Change: ₱ ' + change.toLocaleString('en-US', {maximumFractionDigits:2})"></div>
                            </div>
                        </div>
                        @push('scripts')
                            <script>
                                document.addEventListener('alpine:init', () => {
                                    Alpine.data('checkIn', () => ({
                                        pay: 0, 
                                        senior: false, 
                                        scount: 0,
                                        st: {{$r_list->getServiceTotal()}},
                                        cintotal: {{$r_list->getTotal()}},
                                        balance: {{$r_list->balance()}},
                                        change: {{$r_list->refund()}},
                                        CIN() {
                                            this.ra = {{$r_list->getRoomAmount(true)}};
                                        
                                            this.cintotal = this.st;
                                            this.balance = this.cintotal;
                                            let orig_balance = this.balance;
                                            if(this.balance >= this.pay && !(Math.sign(this.pay) == -1)) this.balance = Math.abs(this.balance - this.pay);
                                            else this.balance = 0;
                        
                                            if(this.balance > this.change) this.change = 0;
                        
                                            if(orig_balance < this.pay) this.change = Math.abs(orig_balance - this.pay);
                                            else this.change = 0;
                        
                                            // if this.balance = 0;
                        
                                            
                                        },
                                    }));
                                });
                            </script>
                        @endpush
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