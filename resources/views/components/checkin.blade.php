@props(['id' => 'checkin', 'name', 'datas' => ''])
<x-modal  id="{{$id}}" title="Change Status Check-in for {{$name}}">
    <article>
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
        <div class="p-5">
            <p class="text-sm md:text-lg"><strong>Total: </strong>₱ {{number_format($datas->getTotal() ?? 0, 2)}}</p>
            <p class="text-sm md:text-lg"><strong>Downpayment: </strong>₱ {{number_format($datas->downpayment() ?? 0, 2)}}</p>
            <p class="text-sm md:text-lg"><strong>Balance: </strong>{{$datas->balance() != 0 ? '₱ ' . number_format($datas->balance(), 2) : 'No Balance'}}</p>
            <p class="text-sm md:text-lg"><strong>Refund: </strong>{{$datas->refund() != 0 ? '₱ ' . number_format($datas->refund(), 2) : 'No Refund'}}</p>
        
            <div x-data="{pay: '', senior: false }" class="py-3 space-x-2">
                <form id="cinf" action="{{route('system.reservation.show.checkin', encrypt($datas->id))}}" method="post">
                    @csrf
                    @method('PUT')
                    @if(!empty($datas->downpayment()) && $datas->downpayment() >= 1000)
                        <div class="mb-5 mt-3">
                            <input id="discount" x-model="senior" type="checkbox" class="checkbox checkbox-secondary checkbox-sm md:checkbox-md" />
                            <label for="discount" class="text-sm md:text-lg ml-4 font-semibold">Have Senior Citizen?</label>
                            <template x-if="senior">
                                <div class="my-3">
                                    <x-input type="number" name="senior_count" id="senior_count" placeholder="Count of Senior Guest" value="{{old('senior_count')}}" />
                                </div>
                            </template>
                        </div>
                        <h3 class="font-bold text-sm md:text-lg">Payment Type</h3>
                        <div class="py-3 space-x-2 text-sm md:text-lg">
                            <input type="radio" x-model="pay" id="partial" name="payments" class="radio radio-primary radio-sm md:radio-md" value="partial" />
                            <label for="partial">Partial</label>
                            <input type="radio" x-model="pay" id="full_payment" name="payments" class="radio radio-primary radio-sm md:radio-md" value="fullpayment" />
                            <label for="full_payment">Full Payment</label>
                        </div>
                        <template x-if="pay == 'partial'">
                            <div class="">
                                <x-input type="number" name="another_payment" id="another_payment" placeholder="Amount" value="{{old('another_payment')}}" />
                            </div>
                        </template>
                        <div x-show="pay != ''" class="modal-action">
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
</x-modal>