@props(['id' => 'checkout', 'name', 'datas' => ''])
<x-modal  id="{{$id}}" title="Check-out for {{$name}}">
    @if( ((int)str_replace('-', '', \Carbon\Carbon::now()->format('Y-m-d'))) >=  ((int)str_replace('-', '', $datas['check_out'])))
        <article>
            <h1>If you want to request an extension, go to the 'extend' module.</h1>
        </article>
    @else
        <article x-data="checkIn">
            <ul role="list" class="marker:text-primary list-disc pl-5 space-y-3 text-neutral">
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
            <div x-data="{pay: 0, st: {{$datas->getServiceTotal()}}, dw: {{$datas->downpayment()}}, ra: {{$datas->getRoomAmount()}}, cinp: {{$datas->checkInPayment()}}, couttotal: {{$datas->getTotal()}}, balance: {{$datas->balance()}}, change: 0}"  class="p-5">
                <p class="text-sm"><strong>Services Total: </strong> <span x-text="st > 0 ? '₱ ' + st.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                <p class="text-sm"><strong>Room Rate Amount: </strong> <span x-text="ra > 0 ? '₱ ' + ra.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                <p class="text-sm"><strong>Total Cost: </strong> <span x-text="couttotal > 0 ? '₱ ' + couttotal.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                <p class="text-sm"><strong>Downpayment: </strong> <span x-text="dw > 0 ? '₱ ' + dw.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                <p class="text-sm"><strong>Payment After Check-in: </strong> <span x-text="cinp > 0 ? '₱ ' + cinp.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                <p class="text-sm"><strong>Balance: </strong> <span x-text="balance > 0 ? '₱ ' + balance.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
            
                <div class="mt-3 space-x-2">
                    <form action="{{route('system.reservation.show.checkout', encrypt($datas->id))}}" action="{{route('system.reservation.show.checkin', encrypt($datas->id))}}" method="post">
                        @csrf
                        @method('PUT')
                        <x-input x-model="pay" type="number" name="coutpay" id="another_payment" placeholder="Amount" value="{{old('another_payment')}}" input="COUT()" />
                        <div  x-show="change != 0" class="text-error" x-text="'Change: ₱ ' + change.toLocaleString('en-US', {maximumFractionDigits:2})"></div>
                        <div x-show="pay" class="modal-action">
                            <button type="submit" class="btn btn-primary">Proceed</button>
                        </div>    
                    </form>
                </div>
            </div>
        </article>

    @endif
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('checkIn', () => ({
                pay: 0, 
                st: {{$datas->getServiceTotal()}},
                dw: {{$datas->downpayment()}},
                ra: {{$datas->getRoomAmount()}},
                cinp: {{$datas->checkInPayment()}},
                cintotal: {{$datas->getTotal()}},
                balance: {{$datas->balance()}},
                balance: {{$datas->balance()}},
                change: 0,
                COUT() {
                    this.couttotal = this.st + this.ra
                    this.balance = this.couttotal - this.dw - this.cinp;
                    let orig_balance = this.balance;
                    if(this.balance >= this.pay) this.balance = Math.abs(this.balance - this.pay);
                    else this.balance = 0;

                    if(this.balance < this.pay) this.change = Math.abs(orig_balance - this.pay);
                    else this.change = 0;
                    
                },
            }));
        });
    </script>
</x-modal>