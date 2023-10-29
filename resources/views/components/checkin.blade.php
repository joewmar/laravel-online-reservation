@props(['id' => 'checkin', 'name', 'datas' => ''])
<x-modal  id="{{$id}}" title="Change Status Check-in for {{$name}}">
    <article x-data="checkIn" >
        <ul role="list" class="marker:text-primary list-disc pl-5 space-y-3 text-neutral text-sm md:text-lg">
            <li><strong>Type: </strong> {{$datas->accommodation_type ?? 'None' }} </li>
            <li><strong>Payment Method: </strong> {{$datas->payment_method ?? 'None'}}</li>
            <li><strong>Number of Guest: </strong> {{$datas->pax ?? 'None'}}</li>
            @php
                if(isset($datas->roomid)){
                    foreach($datas->roomid as $item){
                        $room = \App\Models\Room::findOrFail($item);
                        $rooms[] = 'Room No. ' . $room->room_no ?? 'None' . ' ('.$room->room->name.')';
                    }
                }

            @endphp
            @if(isset($datas->roomid))
                <li><strong>Room No: </strong> {{ implode(',', $rooms ?? [])}}</li>
            @endif
            <li><strong>Check-in: </strong> {{Carbon\Carbon::createFromFormat('Y-m-d', $datas['check_in'])->format('l, F j, Y') ?? 'None'}}</li>
            <li><strong>Check-out: </strong> {{Carbon\Carbon::createFromFormat('Y-m-d', $datas['check_out'])->format('l, F j, Y') ?? 'None'}}</li>
        </ul>
        <div x-data="{pay: 0, senior: false, scount: 0, st: {{$datas->getServiceTotal()}}, dw: {{$datas->downpayment()}}, ra: {{$datas->getRoomAmount()}}, cintotal: {{$datas->getTotal()}}, balance: {{$datas->balance()}}, change: 0}"  class="p-5">
            <p class="text-sm"><strong>Services Total: </strong> <span x-text="st > 0 ? '₱ ' + st.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
            <p class="text-sm"><strong>Room Rate Amount: </strong> <span x-text="ra > 0 ? '₱ ' + ra.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
            <p class="text-sm"><strong>Total Cost: </strong> <span x-text="cintotal > 0 ? '₱ ' + cintotal.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
            <p class="text-sm"><strong>Downpayment: </strong> <span x-text="dw > 0 ? '₱ ' + dw.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
            <p class="text-sm"><strong>Balance: </strong> <span x-text="balance > 0 ? '₱ ' + balance.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
        
            <div class="py-3 space-x-2">
                <form id="cinf" action="{{route('system.reservation.show.checkin', encrypt($datas->id))}}" method="post">
                    @csrf
                    @method('PUT')
                    @if(!empty($datas->downpayment()) && $datas->downpayment() >= 1000)
                        <div class="mb-5 mt-3">
                            <input id="discount" @change="CIN()" x-model="senior" type="checkbox" class="checkbox checkbox-secondary checkbox-sm md:checkbox-md" />
                            <label for="discount" class="text-sm md:text-lg ml-4 font-semibold">Have Senior Citizen?</label>
                            <template x-if="senior">
                                <div class="my-3">
                                    <x-input x-model="scount" type="number" name="senior_count" id="senior_count" placeholder="Count of Senior Guest" value="{{old('senior_count')}}" input="CIN()" />
                                </div>
                            </template>
                        </div>
                        <x-input x-model="pay" type="number" name="another_payment" id="another_payment" placeholder="Amount" value="{{old('another_payment')}}" input="CIN()" />
                        <div x-show="change > 0" class="text-error" x-text="'Change: ₱ ' + change.toLocaleString('en-US', {maximumFractionDigits:2})"></div>
                        <div x-show="pay" class="modal-action">
                            <button type="submit" class="btn btn-primary">Proceed</button>
                        </div>
                    @else
                        <div class="my-3">
                            <span class="text-error">Sorry, the downpayment has not been paid yet</span>
                        </div>
                    @endif

                </form>
            </div>
        </div>
    </article>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('checkIn', () => ({
                pay: 0, 
                senior: false, 
                scount: 0,
                st: {{$datas->getServiceTotal()}},
                dw: {{$datas->downpayment()}},
                ra: {{$datas->getRoomAmount()}},
                cintotal: {{$datas->getTotal()}},
                balance: {{$datas->balance()}},
                balance: {{$datas->balance()}},
                change: 0,
                CIN() {
                    this.ra = {{$datas->getRoomAmount()}};
                    if(this.senior == true && this.scount != 0){
                        discounted = (20 / 100) * this.scount;
                        discounted = this.ra * discounted;
                        discounted = this.ra - discounted;
                        this.ra = discounted;
                    }
                    this.cintotal = this.st + this.ra
                    this.balance = this.cintotal - this.dw;
                    let orig_balance = this.balance;
                    if(this.balance >= this.pay) this.balance = Math.abs(this.balance - this.pay);
                    else this.balance = 0;

                    if(orig_balance < this.pay) this.change = Math.abs(orig_balance - this.pay);
                    else this.change = 0;
                    
                },
            }));
        });
    </script>
</x-modal>