@props(['id' => 'checkout', 'name', 'datas' => ''])
<x-modal  id="{{$id}}" title="Check-out for {{$name}}">
    @if( ((int)str_replace('-', '', \Carbon\Carbon::now()->format('Y-m-d'))) >=  ((int)str_replace('-', '', $datas['check_out'])))
        <article>
            <h1>If you want to request an extension, go to the 'extend' module.</h1>
        </article>
    @else
        <article>
            <ul role="list" class="marker:text-primary list-disc pl-5 space-y-3 text-neutral">
                <li><strong>Type: </strong> {{$datas->accommodation_type ?? 'None' }} </li>
                <li><strong>Payment Method: </strong> {{$datas->payment_method ?? 'None'}}</li>
                <li><strong>Number of Guest: </strong> {{$datas->pax ?? 'None'}}</li>
                @php
                    foreach($datas->roomid as $item){
                        $room = \App\Models\Room::findOrFail($item);
                        $rooms[] = 'Room No. ' . $room->room_no ?? 'None' . ' ('.$room->room->name.')';
                    }
                @endphp
                <li><strong>Room No: </strong> {{ implode(',', $rooms ?? [])}}</li>
                <li><strong>Check-in: </strong> {{Carbon\Carbon::createFromFormat('Y-m-d', $datas['check_in'])->format('l, F j, Y') ?? 'None'}}</li>
                <li><strong>Check-out: </strong> {{Carbon\Carbon::createFromFormat('Y-m-d', $datas['check_out'])->format('l, F j, Y') ?? 'None'}}</li>
            </ul>
            <div class="p-5">
                <p class="text-lg"><strong>Total: </strong>₱ {{number_format($datas->getTotal() ?? 0, 2)}}</p>
                {{-- <p class="text-lg"><strong>Downpayment: </strong>₱ {{number_format($datas->downpayment ?? 0, 2)}}</p> --}}
                <p class="text-lg"><strong>Balance: </strong>₱ {{number_format($datas->balance() ?? 0, 2)?? 'No Balance'}}</p>
                <div class="py-3 space-x-2">
                    <form action="{{route('system.reservation.show.checkout', encrypt($datas->id))}}" x-data="{isFullPay: {{($datas->balance() ?? 0) <= 0 ? 'true' : 'false'}} }" action="{{route('system.reservation.show.checkin', encrypt($datas->id))}}" method="post">
                        @csrf
                        @method('PUT')
                        <div class="py-3 space-x-2">
                            <input x-model="isFullPay" type="checkbox" id="isFullPaid" name="fullpay" class="checkbox checkbox-sm checkbox-primary" value="true" /> 
                            <label for="isFullPaid">Full Paid</label>
                            @error('fullpay')
                                 <label class="text-error text-lg">{{$message}}</label>
                            @enderror
                        </div>
                        <div x-show ="isFullPay" class="modal-action" x-transition >
                            <button @click="loader = true" class="btn btn-primary">Check-out</button>
                        </div>
                    </form>
                </div>
            </div>
        </article>

    @endif

</x-modal>