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
            <div x-data="{pay: 0, tr: {{$datas->getTourTotal(true)}}, ad: {{$datas->getAddonTotal(true)}}, dw: {{$datas->downpayment()}}, ra: {{$datas->getRoomAmount()}}, cinp: {{$datas->checkInPayment()}}, couttotal: {{$datas->getTotal()}}, balance: {{$datas->balance()}}, change: {{$datas->refund()}}}"  class="p-5">
                <p class="text-sm {{$datas->getTourTotal(true) > 0 ? 'line-through' : ''}} "><strong>Tour Amount: </strong>{{$datas->getTourTotal() > 0 ? '₱ ' . number_format($datas->getTourTotal(), 2) : 'None' }}</p>
                <p class="text-sm"><strong>Tour Amount (Computed): </strong> <span x-text="tr > 0 ? '₱ ' + tr.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                <p class="text-sm"><strong>Addon Amount: </strong> <span x-text="ad > 0 ? '₱ ' + ad.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                <p class="text-sm"><strong>Room Rate Amount: </strong> <span x-text="ra > 0 ? '₱ ' + ra.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                <p class="text-sm"><strong>Total Cost: </strong> <span x-text="couttotal > 0 ? '₱ ' + couttotal.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                <p class="text-sm"><strong>Downpayment: </strong> <span x-text="dw > 0 ? '₱ ' + dw.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                <p class="text-sm"><strong>Payment After Check-in: </strong> <span x-text="cinp > 0 ? '₱ ' + cinp.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                <p class="text-sm"><strong>Balance: </strong> <span x-text="balance > 0 ? '₱ ' + balance.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                <p class="text-sm"><strong>Change: </strong> <span x-text="change > 0 ? '₱ ' + change.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
            
                <div class="mt-3 space-x-2">
                    <form id="ctfrm" action="{{route('system.reservation.show.checkout', encrypt($datas->id))}}" action="{{route('system.reservation.show.checkin', encrypt($datas->id))}}" method="post">
                        @csrf
                        @method('PUT')
                        <div class="flex justify-end">
                            <div class="dropdown dropdown-end">
                                <label tabindex="0" class="btn btn-circle btn-ghost btn-xs text-info-content">
                                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-4 h-4 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </label>
                                <div tabindex="0" class="card compact dropdown-content z-[1] shadow bg-base-100 rounded-box w-64">
                                  <div class="card-body">
                                    <h2 class="card-title">Note!</h2> 
                                    <p>If you want to force check-out, just type "force-[passcode]"</p>
                                  </div>
                                </div>
                              </div>
                        </div>
                        <x-input x-model="pay" name="coutpay" id="another_payment" placeholder="Amount" value="{{old('another_payment')}}" input="COUT()" autocomplete="off" />
                        <div x-show="pay || balance == 0" class="modal-action">
                            <label for="ctmdl" class="btn btn-primary">Proceed</label>
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
                tr: {{$datas->getTourTotal(true)}},
                ad: {{$datas->getAddonTotal()}},
                dw: {{$datas->downpayment()}},
                ra: {{$datas->getRoomAmount()}},
                cinp: {{$datas->checkInPayment()}},
                couttotal: this.tr + this.ad + this.ra,
                @if($datas->getTourTotal(true))
                    balance: this.couttotal,
                @else
                     balance: {{$datas->balance()}},
                @endif
                change: {{$datas->refund()}},
                COUT() {
                    this.couttotal = this.tr + this.ad + this.ra
                    this.balance = this.couttotal - this.dw - this.cinp;
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
<x-modal id="ctmdl" title="Do you want to proceed check-out" type="YesNo" formID="ctfrm" loader>
</x-modal >