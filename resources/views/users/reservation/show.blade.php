@php
    $total = 0;
    $downpayment = 0;
    $dscPerson = 0;

    foreach ($r_list->transaction ?? [] as $outerKey => $outerItem) {
        if ($outerKey == 'payment' && is_array($outerItem)) {
            $downpayment = $outerItem['downpayment'] ?? 0;
            $dscPerson = $outerItem['discountPerson'] ?? 0;
            continue;
        }
    }
    $arrAccType = ['Room Only', 'Day Tour', 'Overnight'];
    $arrAccTypeTitle = ['Room Only', 'Day Tour (Only 1 Day)', 'Overnight (Only 2 days and above)'];
    $arrPayment = ['Gcash', 'PayPal', 'Bank Transfer'];

@endphp

<x-landing-layout noFooter>
    <x-navbar :activeNav="$activeNav" type="plain"/>
    <x-full-content>
        <section class="px-5 md:px-20 pt-24">
            {{-- User Details --}}
            <a href="{{route('user.reservation.home')}}" class="btn btn-ghost btn-circle">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="px-3 md:px-20">
                <x-profile :rlist="$r_list" />
                <div class="flex md:hidden justify-end">
                    <div class="dropdown dropdown-top dropdown-end">
                        <label tabindex="0" class="btn btn-ghost btn-circle">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-5 h-5 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"></path></svg>
                        </label>
                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                            <li>
                                <a href="{{route('user.reservation.show.online.payment', encrypt($r_list->id))}}">
                                    Online Payment
                                </a>
                            </li>
                            @if($r_list->status === 3)
                                <li>
                                    <a href="{{route('reservation.receipt', encrypt($r_list->id))}}">
                                        Reciept
                                    </a>
                                </li>
                            @endif
                            </ul>
                      </div>
                </div>
                <div class="w-full hidden md:flex justify-end join">
                    <a href="{{route('user.reservation.show.online.payment', encrypt($r_list->id))}}" class="join-item btn btn-info btn-sm">
                        Online Payment
                    </a>
                    @if($r_list->status === 3)
                        <a href="{{route('reservation.receipt', encrypt($r_list->id))}}" class="join-item btn btn-success btn-sm">
                            <i class="fa-solid fa-receipt"></i>
                            Reciept
                        </a>
                    @endif
                    <a href="{{route('user.reservation.edit.step1', encrypt($r_list->id))}}" class="join-item btn btn-sm {{$r_list->status > 0 ? 'btn-disabled' : ''}}">
                        Edit
                    </a>
                </div>
                <div class="divider"></div>
                <div class="block">
                    <article class="text-md tracking-tight text-neutral my-5 w-auto">
                        <h2 class="text-xl md:text-2xl mb-5 font-bold">Details</h2>
                        <div class="overflow-x-auto">
                            <table class="table table-xs md:table-sm">
                              <!-- head -->
                              <tbody>
                                <!-- row 1 -->
                                
                                <tr>
                                  <th>Number of Guest</th>
                                  <td>{{$r_list->pax . ' guest' ?? 'None'}}</td>
                                  <td></td>
                                  <td></td>
                                </tr>
        
                                @if(!empty($r_list->tour_pax))
                                    <tr>
                                        <th>Guest going on tour</th>
                                        <td>{{$r_list->tour_pax . ' guest' ?? 'None'}}</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>Type</th>
                                    <td>{{$r_list->accommodation_type ?? 'None'}}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th>Room No</th>
                                    <td>{{!empty($rooms) ? $rooms : 'None'}}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th>Check-in</th>
                                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m-d', $r_list->check_in )->format('l, F j, Y') ?? 'None'}}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th>Check-out</th>
                                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m-d', $r_list->check_out )->format('l, F j, Y') ?? 'None'}}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th>Payment Method</th>
                                    <td>{{ $r_list->payment_method ?? 'None'}}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>{{ $r_list->status() ?? 'None'}}</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                              </tbody>
                            </table>
                        </div>
                        
                    </article>
                    <article>
                        @if(!empty($menu))
                            <div class="w-auto">
                                <div class="overflow-x-auto">
                                    <table class="table table-xs md:table-sm w-full">
                                    <!-- head -->
                                    <thead>
                                        <tr class="text-neutral font-bold">
                                            <td>Used</td>
                                            <td>Tours</td>
                                            <td>Price</td>
                                            <td>Quantity</td>
                                            <td>Amount</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $totalTour = 0 @endphp
                                        @foreach ($menu as $key => $item)
                                            <tr >
                                                <td>
                                                    @if($item['used'])
                                                        <i class="fa-solid fa-check text-primary"></i>
                                                    @else
                                                        <i class="fa-solid fa-x text-error"></i>
                                                    @endif
                                                </td>
                                                <td>{{$item['title']}}</td> 
                                                <td>₱ {{number_format($item['price'], 2)}}</td> 
                                                <td class="text-center">{{$item['tpx']}} guest</td> 
                                                <td>₱ {{number_format($item['amount'], 2)}}</td> 
                                            </tr>
                                            @php $totalTour += $item['amount'] @endphp
        
                                        @endforeach
                                        <tr class="bg-base-200">
                                            <td></td> 
                                            <td></td> 
                                            <td></td> 
                                            <td class="font-bold text-right">Total</td> 
                                            <td colspan="2" class="font-bold">₱ {{number_format($totalTour, 2)}}</td> 
                                        </tr>
                                    </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </article>
                </div>
                @if(!empty($other_addons))
                    <div class="divider"></div>
                    <article>
                        <h1 class="my-1 text-lg md:text-xl "><strong>Additional Request: </strong></h1>
                        @if(!empty($other_addons))
                            <div class="my-5 w-full">
                                <div class="overflow-x-auto">
                                    <table class="table table-xs md:table-sm">
                                        <!-- head -->
                                        <thead>
                                            <tr>
                                                <th>Addons</th>
                                                <th>Pcs</th>
                                                <th>Price</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $totalAddons = 0 @endphp
                                            @foreach ($other_addons as $key => $addon)
                                                <tr>
                                                    <td>{{$addon['title']}}</td> 
                                                    <td>{{$addon['pcs'] ?? 0}} pcs</td> 
                                                    <td>₱ {{number_format($addon['price'], 2)}}</td> 
                                                    <td>₱ {{number_format($addon['amount'], 2)}}</td> 
                                                </tr>
                                                @php $totalAddons += $addon['amount'] @endphp
        
                                            @endforeach
                                            <tr class="bg-base-200">
                                                <td></td> 
                                                <td></td> 
                                                <td class="font-bold">Total</td> 
                                                <td class="font-bold">₱ {{number_format($totalAddons, 2)}}</td> 
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </article>
                @endif
                <div class="divider"></div>
                <article class="text-md tracking-tight text-neutral my-5 w-auto">
                    <h2 class="text-xl md:text-2xl mt-5 font-bold">Transaction</h2>
                    <div class="overflow-x-auto">
                        <table class="table table-xs md:table-sm">
                            <tbody>
                                <tr @if($r_list->status == 3) class="line-through" @endif>
                                    <th>Tour Cost:</th>
                                    <td>{{$r_list->getTourTotal(false) > 0 ? '₱ ' . number_format($r_list->getTourTotal(false) ?? 0, 2) : 'None' }}</td>
                                </tr>
                                <tr>
                                    <th>Tour Used Cost: </th>
                                    <td>{{$r_list->getTourTotal(true) > 0 ? '₱ ' .  number_format($r_list->getTourTotal(true) ?? 0, 2) : 'None' }}</td>
                                    @php $total += $r_list->getTourTotal() @endphp
                                </tr>
                                <tr>
                                    <th>Addons Cost:</th>
                                    <td>{{$r_list->getAddonTotal() > 0 ? '₱ '.  number_format($r_list->getAddonTotal() ?? 0, 2) : 'None' }}</td>
                                    @php $total += $r_list->getAddonTotal() @endphp
                                </tr>
                                @if ($dscPerson !== 0)
                                    <tr>
                                        <th>Room Rate (Orginal Price)</th>
                                        <td>{{$rate['name'] ?? ''}} -  ₱ {{ number_format((double)$rate['price'] ?? 0, 2) }}</td>
                                    </tr>
                                    <tr class="text-md tracking-tight text-neutral">
                                        <th >No. of days: </th>
                                        <td>{{$r_list->getNoDays() > 1 ? $r_list->getNoDays() . ' days' : $r_list->getNoDays() . ' day'}}</td>
                                    </tr>
                                    <tr>
                                        <th>Rate Amount per Person: </th>
                                        <td>₱ {{ number_format($rate['person'], 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Rate Amount (Orginal):</th>
                                        <td>₱ {{ number_format($rate['orig_amount'], 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Senior / PWD Guest</th>
                                        <td>{{$dscPerson ?? 0}} Guest</td>
                                    </tr>
                                    <tr>
                                        <th>Discount</th>
                                        <td>20%</td>
                                    </tr>
                                    <tr>
                                        <th>Room Rate Discounted</th>
                                        <td>₱ {{ number_format($rate['discounted'] , 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Total of Room Rate with Discounted</th>
                                        <td>₱ {{ number_format($rate['amount'], 2) }}</td>
                                    </tr>
                                @endif
                                @if($r_list->status > 0 && $r_list->status <= 3 && $dscPerson === 0)
                                    <tr>
                                        <th>Room Rate:</th>
                                        <td>{{$rate['name'] ?? ''}} -  ₱ {{ number_format((double)$rate['price'] ?? 0, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th >No. of days: </th>
                                        <td>{{$r_list->getNoDays() > 1 ? $r_list->getNoDays() . ' days' : $r_list->getNoDays() . ' day'}}</td>
                                    </tr>
                                    <tr>
                                        <th>Rate Amount per Person: </th>
                                        <td>₱ {{ number_format($rate['person'], 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Rate Amount: </th>
                                        <td>₱ {{ number_format($rate['amount'], 2) }}</td>
                                    </tr>
                                @endif
                                @php $total += $r_list->getRoomAmount() @endphp
                            <tr>
                                <th>Total Cost: </th>
                                <td>{{$total > 0 ? '₱ ' . number_format($total, 2) : 'None' }}</td>
                            </tr>
                            <tr>
                                <th>Downpayment: </th>
                                <td class="flex items-center gap-4">
                                    {{$r_list->downpayment() !== 0 ? '₱ ' . number_format($r_list->downpayment(), 2) : 'None'}}
                                </td>
                            </tr>
                            <tr>
                                <th>Payment after Check-in: </th>
                                <td class="flex items-center gap-4">
                                    {{$r_list->checkInPayment() !== 0 ? '₱ ' . number_format($r_list->checkInPayment(), 2) : 'None' }}
                                </td>
                            </tr>
                            <tr>
                                <th>Payment after Check-out: </th>
                                <td>{{$r_list->checkOutPayment() != 0 ? '₱ ' . number_format($r_list->checkOutPayment(), 2) : 'None' }}</td>
                            </tr>
                            <tr >
                                <th>Balance due: </th>
                                <td>{{$r_list->balance() !== 0 ? '₱ ' . number_format($r_list->balance(), 2) : 'None' }}</td>
                            </tr>
                            <tr >
                                <th>Refund: </th>
                                <td>{{$r_list->refund() !== 0 && !(isset($r_list->transaction['payment']['refunded']) && $r_list->transaction['payment']['refunded'] == true) ? '₱ ' . number_format($r_list->refund(), 2) : 'No Refund' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </article>
                
                <div class="divider"></div>
                <div x-data="{show: false}" class=" w-full">
                    <div class="flex items-start space-x-2">
                        <h2 class="text-xl md:text-2xl mb-5 font-bold">Valid ID</h2> 
                        <button @click="show = !show" type="button" x-text="show ? 'Hide' : 'Show' " class="btn btn-primary btn-sm"></button>
                    </div>
                    <template x-if="show">
                        <div class="w-full rounded flex justify-center">
                            @if($r_list->userReservation->valid_id)
                                <img src="{{route('private.image', ['folder' => explode('/', $r_list->userReservation->valid_id)[0], 'filename' => explode('/', $r_list->userReservation->valid_id)[1]])}}" alt="Valid ID of {{$r_list->userReservation->name()}}">
                            @else
                                <h2 class="text-xl font-bold ">No Valid ID Send</h2>
                            @endif
                        </div>
                    </template>
                </div>
                <div class="divider"></div>

                <div class="w-full mb-10">
                    <h2 class="text-xl md:text-2xl mb-5 font-bold">Message</h2>
                    <div tabindex="0" class="collapse collapse-arrow border border-base-300 bg-base-200">
                        <div class="collapse-title text-lg md:text-xl font-medium">
                            <span>Request Message</span>
                            <label for="edit_service" class="btn btn-sm btn-ghost btn-circle btn-primary" {{!isset($r_list->message['request']) ? 'disabled' : ''}}>
                                <i class="fa-solid fa-pen-to-square"></i>
                            </label>
                        </div>
                        <div class="collapse-content"> 
                          <p>{{$r_list->message['request'] ?? 'None'}}</p>
                        </div>
                    </div>
                    <div tabindex="0" class="collapse collapse-arrow border border-base-300 bg-base-200">
                        <div class="collapse-title text-lg md:text-xl font-medium">
                            <span>Cancel Request</span>
                            <label for="edit_cancel" class="btn btn-sm btn-ghost btn-circle btn-primary" {{!isset($r_list->message['cancel']) ? 'disabled' : ''}}>
                                <i class="fa-solid fa-pen-to-square"></i>
                            </label>
                        </div>
                        <div class="collapse-content"> 
                          <p>{{$r_list->message['cancel']['message'] ?? 'None'}}</p>
                        </div>
                    </div>
                    <div tabindex="0" class="collapse collapse-arrow border border-base-300 bg-base-200">
                        <div class="collapse-title text-lg md:text-xl font-medium">
                            <span>Reschedule Request</span>
                            <label for="edit_reschedule" class="btn btn-sm btn-ghost btn-circle btn-primary" {{!isset($r_list->message['reschedule']) ? 'disabled' : ''}}>
                                <i class="fa-solid fa-pen-to-square"></i>
                            </label>
                        </div>
                        <div class="collapse-content"> 
                        @if(isset($r_list->message['reschedule']['check_in']))
                            <span class="text-md my-2 font-bold">Check-in Request: </span><span>{{$r_list->message['reschedule']['check_in']}}</span><br>
                        @endif
                        @if(isset($r_list->message['reschedule']['check_out']))
                            <span class="text-md my-2 font-bold">Check-out Request: </span><span>{{$r_list->message['reschedule']['check_out']}}</span><br>
                        @endif
                        @if(isset($r_list->message['reschedule']['message']))
                            <div class="text-md mt-2 font-bold">Message</div>
                        @endif
                          <p>{{$r_list->message['reschedule']['message'] ?? 'None'}}</p>
                        </div>
                    </div>
                    @if(isset($r_list->message['request']))
                        <x-modal id="edit_service" title="Edit Service Request"> 
                            <form action="{{route('user.reservation.update.request', encrypt($r_list->id))}}" method="POST">
                                @csrf
                                @method('PUT')
                                <x-textarea name="service_message" id="service_message" placeholder="Request" value="{{$r_list->message['request']}}" />
                                <div class="modal-action">
                                <button class="btn btn-primary">Save</button>
                                </div>
                            </form>
                        </x-modal> 
                    @endif
                    @if(isset($r_list->message['cancel']))
                        <x-modal id="edit_cancel" title="Edit Cancel Request"> 
                            <form action="{{route('user.reservation.update.cancel', encrypt($r_list->id))}}" method="POST">
                                @csrf
                                @method('PUT')
                                <x-textarea name="cancel_message" id="cancel_message" placeholder="Reason" value="{{$r_list->message['cancel']['message']}}" />
                                <div class="modal-action">
                                <button class="btn btn-primary">Save</button>
                                </div>
                            </form>
                        </x-modal> 
                    @endif
                    @if(isset($r_list->message['reschedule']))
                        <x-modal id="edit_reschedule" title="Edit Reschedule Request"> 
                            <form action="{{route('user.reservation.update.reschedule', encrypt($r_list->id))}}" method="POST">
                            @csrf
                            @method('PUT')
                            <p class="my-5">
                                <span class="font-medium">Type: {{$r_list->accommodation_type}}</span>
                            </p>
                            <x-textarea name="reschedule_message" id="reschedule_message" placeholder="Reason to Reschedule Reservation" value="{{$r_list->message['reschedule']['message'] ?? ''}}"/>
                            <x-datetime-picker name="check_in" id="check_in" placeholder="Check in" class="flatpickr-reservation" value="{{$r_list->message['reschedule']['check_in'] ?? ''}}"/>
                            <x-datetime-picker name="check_out" id="check_out" placeholder="Check out" class="flatpickr-reservation flatpickr-input2" value="{{$r_list->message['reschedule']['check_out'] ?? ''}}" />
                            <div class="modal-action">
                                <button class="btn btn-warning">Save</button>
                            </div>
                            </form>
                        </x-modal> 
                    @endif
                </div>
                @if($r_list->status == 0 || $r_list->status >= 3)
                    <div class="flex justify-end mb-5">
                        <label for="delete_rsv" class="btn btn-error btn-sm">
                            Delete Reservation
                        </label>
                    </div>
                    <x-modal id="delete_rsv" title="If you want to delete this reservation. Enter your password for confirmation">
                        <form action="{{route('user.reservation.destroy', encrypt($r_list->id))}}" method="POST">
                            @csrf
                            @method('DELETE')
                            <x-password />
                            <div class="modal-action">
                                <button class="btn btn-error">Force Delete</button>
                            </div>
                        </form>
                    </x-modal>
                @endif
            </div>
        </section>
    </x-full-content>
    @push('scripts')
        <script>
        @if(isset($from) && isset($to))
            const mop = {
                from: '{{\Carbon\Carbon::createFromFormat('Y-m-d', $from)->format('Y-m-d')}}',
                to: '{{\Carbon\Carbon::createFromFormat('Y-m-d', $to)->format('Y-m-d')}}'
            };
        @else
            const mop = '2001-15-30';
        @endif
        const md = '{{Carbon\Carbon::now()->addDays(2)->format('Y-m-d')}}';
        </script>
        <script type="module" src="{{Vite::asset('resources/js/flatpickr.js')}}"></script>
    @endpush
</x-landing-layout>