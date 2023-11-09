@props(['id' => 'checkin', 'name', 'datas' => ''])
<x-modal  id="{{$id}}" title="Change Status Check-in for {{$name}}">
    <article x-data="checkIn" >
        <ul role="list" class="marker:text-primary list-disc pl-5 space-y-3 text-neutral text-sm md:text-lg">
            <li><strong>Number of Guest: </strong> {{$datas->pax ?? 'None'}}</li>
            @php
                if(isset($datas->roomid)){
                    foreach($datas->roomid as $item){
                        $room = \App\Models\Room::findOrFail($item);
                        $pax = array_key_exists($datas->id, $room->customer) ? $room->customer[$datas->id] : '';
                        $rooms[] = 'Room No. ' . $room->room_no . ' ('.$pax.' guest)';
                    }
                }

            @endphp
            @if(isset($datas->roomid))
                <li><strong>Room No: </strong> {{ implode(',', $rooms ?? [])}}</li>
            @endif
            <li><strong>Guest: </strong> {{$datas->pax}}</li>
            <li><strong>Check-in: </strong> {{Carbon\Carbon::createFromFormat('Y-m-d', $datas['check_in'])->format('l, F j, Y') ?? 'None'}}</li>
            <li><strong>Check-out: </strong> {{Carbon\Carbon::createFromFormat('Y-m-d', $datas['check_out'])->format('l, F j, Y') ?? 'None'}}</li>
        </ul>
        {{-- @if (Carbon\Carbon::createFromFormat('Y-m-d', $datas->check_in)->startOfWeek()->setTimezone('Asia/Manila')->timestamp <= Carbon\Carbon::now()->setTimezone('Asia/Manila')->timestamp) --}}
            <div x-data="{pay: 0, senior: false, scount: 0, dstd: 0,st: {{$datas->getServiceTotal()}}, dw: {{$datas->downpayment()}}, ra: {{$datas->getRoomAmount(false)}}, cintotal: {{$datas->getTotal()}}, balance: {{$datas->balance()}}, change: {{$datas->refund()}}}" class="p-5">
                <p class="text-sm"><strong>Service Cost: </strong> <span x-text="st > 0 ? '₱ ' + st.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                <p class="text-sm"><strong>Rate Amount per Person: </strong> {{$datas->getRoomAmount(false, true) > 0 ? '₱ ' . number_format($datas->getRoomAmount(false, true), 2) : 'None'}}</p>
                <template x-if="senior">
                    <div>
                        <p class="text-sm"><strong>Senior / PWD Guest: </strong> <span x-text="scount > 0 ? scount  : 'None' "></span></p>
                        <p class="text-sm"><strong>Rate Discounted per Person: </strong> <span x-text="dstd > 0 ? '₱ ' + dstd.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                        <p class="text-sm"><strong>Total Rate: </strong> <span x-text="ra > 0 ? '₱ ' + ra.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                    </div>
                </template>
                <p class="text-sm"><strong>Total Cost: </strong> <span x-text="cintotal > 0 ? '₱ ' + cintotal.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                <p class="text-sm"><strong>Downpayment: </strong> <span x-text="dw > 0 ? '₱ ' + dw.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                <p class="text-sm"><strong>Balance: </strong> <span x-text="balance > 0 ? '₱ ' + balance.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
            
                <div class="py-3 space-x-2">
                    <form id="cnfrm" action="{{route('system.reservation.show.checkin', encrypt($datas->id))}}" method="post">
                        @csrf
                        @method('PUT')
                        @if(!empty($datas->downpayment()) && $datas->downpayment() >= 1000)
                            <div class="mb-5 mt-3">
                                <input id="discount" @change="CIN()" x-model="senior" type="checkbox" class="checkbox checkbox-secondary checkbox-sm md:checkbox-md" />
                                <label for="discount" class="text-sm md:text-lg ml-4 font-semibold">Have Senior / PWD Citizen?</label>
                                <template x-if="senior">
                                    <div class="my-3">
                                        <x-input x-model="scount" type="number" name="senior_count" id="senior_count" placeholder="Count of Guest" value="{{old('senior_count')}}" input="CIN()" />
                                    </div>
                                </template>
                            </div>
                            <x-input x-model="pay" type="number" name="another_payment" id="another_payment" placeholder="Amount" value="{{old('another_payment')}}" input="CIN()" />
                            <div x-show="change > 0" class="text-error" x-text="'Change: ₱ ' + change.toLocaleString('en-US', {maximumFractionDigits:2})"></div>
                            <div x-show="pay" class="modal-action">
                                <label for="cnmdl" class="btn btn-primary">Proceed</label>
                            </div>
                        @else
                            <div class="my-3">
                                <span class="text-error">Sorry, the downpayment has not been paid yet</span>
                            </div>
                        @endif

                    </form>
                </div>
            </div>
        {{-- @else   
            <p class="mt-5 text-error">Sorry, Just Wait on {{Carbon\Carbon::createFromFormat('Y-m-d', $datas->check_in)->startOfWeek()->setTimezone('Asia/Manila')->format('F j, Y')}} to activate</p>
        @endif --}}

    </article>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('checkIn', () => ({
                pay: 0, 
                senior: false, 
                scount: 0,
                dstd: 0,
                st: {{$datas->getServiceTotal()}},
                dw: {{$datas->downpayment()}},
                ra: {{$datas->getRoomAmount(false)}},
                rap: {{$datas->getRoomAmount(false, true)}},
                cintotal: {{$datas->getTotal()}},
                balance: {{$datas->balance()}},
                change: {{$datas->refund()}},
                discounted(amount = 0, rate = 20) {
                    let d = 0;
                    d = (rate / 100);
                    d = amount * d;
                    d = amount - d;
                    return d;
                },
                CIN() {
                    let dstd = this.discounted(this.rap);
                    let rnum = 0;
                    if(this.senior == true && this.scount != 0){
                        this.dstd = dstd;
                    }
                    for(i = 1; i <= {{$datas->pax}}; i++){
                        if(i <= this.scount) rnum += parseFloat(this.dstd) ;
                        else rnum += this.rap;
                    }
                    this.ra = rnum;
                    this.cintotal = this.st + this.ra
                    this.balance = this.cintotal - this.dw;
                    let orig_balance = this.balance;
                    if(this.balance >= this.pay && !(Math.sign(this.pay) == -1)) this.balance = Math.abs(this.balance - this.pay);
                    else this.balance = 0;

                    if(this.balance > this.change) this.change = 0;


                    if(orig_balance < this.pay) this.change = Math.abs(orig_balance - this.pay);
                    else this.change = 0;

                    
                },
            }));
        });
    </script>
</x-modal>
<x-modal id="cnmdl" title="Do you want to proceed check-out" type="YesNo" formID="cnfrm" loader>
</x-modal >