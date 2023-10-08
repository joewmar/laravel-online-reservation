@props(['id' => 'checkin', 'name', 'datas' => ''])
<x-modal  id="{{$id}}" title="Check-in for {{$name}}" noBottom>
    @if( ((int)str_replace('-', '', \Carbon\Carbon::now()->format('Y-m-d'))) >=  ((int)str_replace('-', '', $datas['check_in'])))
        <article>
            <h1>Sorry, </h1>
        </article>
    @else
        <article>
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
            <div class="p-5">
                <p class="text-lg"><strong>Total: </strong>₱ {{number_format($datas->getTotal() ?? 0, 2)}}</p>
                <p class="text-lg"><strong>Downpayment: </strong>₱ {{number_format($datas->downpayment() ?? 0, 2)}}</p>
                <p class="text-lg"><strong>Balance: </strong>{{$datas->balance() != 0 ? '₱ ' . number_format($datas->balance(), 2) : 'No Balance'}}</p>
                <p class="text-lg"><strong>Refund: </strong>{{$datas->refund() != 0 ? '₱ ' . number_format($datas->refund(), 2) : 'No Refund'}}</p>
            
                <div x-data="{pay: '', senior: false }" class="py-3 space-x-2">
                    <form id="cinf" action="{{route('system.reservation.show.checkin', encrypt($datas->id))}}" method="post">
                        @csrf
                        @method('PUT')
                        @if(!empty($datas->downpayment()) && $datas->downpayment() >= 1000)
                            <div class="mb-10 mt-3">
                                <input id="discount" x-model="senior" type="checkbox" class="checkbox checkbox-secondary" />
                                <label for="discount" class="ml-4 font-semibold">Have Senior Citizen?</label>
                                <template x-if="senior">
                                    <div class="my-3">
                                        <x-input type="number" name="senior_count" id="senior_count" placeholder="Count of Senior Guest" value="{{old('senior_count')}}" />
                                    </div>
                                </template>
                            </div>
                            <h3 class="font-bold text-lg">Pay</h3>
                            <div class="py-3 space-x-2">
                                <input type="radio" x-model="pay" id="partial" name="payments" class="radio radio-primary" value="partial" />
                                <label for="partial">Partial</label>
                                <input type="radio" x-model="pay" id="full_payment" name="payments" class="radio radio-primary" value="fullpayment" />
                                <label for="full_payment">Full Payment</label>
                                <template x-if="pay == 'partial'">
                                    <div class="my-3">
                                        <x-input type="number" name="another_payment" id="another_payment" placeholder="Amount" value="{{old('another_payment')}}" />
                                    </div>
                                </template>
                            </div>
                            <div x-show="pay != ''" class="modal-action">
                                <label for="cinmld" class="btn btn-primary">Proceed</label>
  
                            </div>
                            <x-modal id="cinmld" title="Do you want to change to check-in" type="YesNo" formID="cinf">
                            </x-modal>

                        @else
                            <div class="my-3">
                                <span class="text-red-500">Sorry, the downpayment has not been paid yet</span>
                            </div>
                        @endif

                    </form>
                </div>
            </div>
        </article>

    @endif

</x-modal>