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
    $arrAccTypeTitle = ['Room Only (Any Date)', 'Day Tour (Only 1 Day)', 'Overnight (Only 2 days and above)'];
    $arrPayment = ['Gcash', 'PayPal', 'Bank Transfer'];

@endphp

<x-landing-layout noFooter>
    <x-navbar :activeNav="$activeNav" type="plain"/>
    <x-full-content>
        <section class="px-5 md:px-20 pt-24">
            {{-- User Details --}}
            <a href="{{URL::previous()}}" class="btn btn-ghost btn-circle">
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
                            @if($r_list->status == 0 || $r_list->status >= 3)
                                <li>
                                    <label for="delete_rsv">
                                        Delete Reservation
                                    </label>
                                </li>
                            @endif
                            </ul>
                      </div>
                </div>
                <div class="w-full hidden md:flex justify-end space-x-1">
                    <a href="{{route('user.reservation.show.online.payment', encrypt($r_list->id))}}" class="btn btn-info btn-sm">
                        Online Payment
                    </a>
                    @if($r_list->status === 3)
                        <a href="{{route('reservation.receipt', encrypt($r_list->id))}}" class="btn btn-success btn-sm">
                            <i class="fa-solid fa-receipt"></i>
                            Reciept
                        </a>
                    @endif
                    @if($r_list->status == 0 || $r_list->status >= 3)
                        <label for="delete_rsv" class="btn btn-error btn-sm">
                            Delete Reservation
                    </label>
                    @endif
                </div>
                @if($r_list->status == 0 || $r_list->status >= 3)
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
                <div class="divider"></div>
                <div class="block">
                    <article class="text-md tracking-tight text-neutral my-5 w-auto">
                            <div class="flex gap-4 mb-5 items-center" >
                                <h2 class="text-xl md:text-2xl font-bold">Details</h2>
                                    <label for="edit_modal" class="btn btn-sm btn-primary" {{$r_list->status > 0 ? 'disabled' : ''}}>Change</label>
                                @if($r_list->status > 0)
                                    <x-modal title="Change Information" id="edit_modal">
                                        <p>You cannot change it once your reservation has been confirmed.</p>
                                    </x-modal>
                                @else
                                    <x-modal title="Change Information" id="edit_modal">
                                        <form x-data="{at: '{{$r_list->accommodation_type}}'}" action="{{route('user.reservation.update.details', encrypt($r_list->id))}}" method="post">
                                            @csrf
                                            @method('PUT')
                                            <div class="mt-4 ">
                                              <div class="space-y-4 lg:block">
                                                    <x-datetime-picker name="check_in" id="check_in" placeholder="Check in" class="flatpickr-reservation" value="{{$r_list->check_in}}" noRequired />
                                                    <x-datetime-picker name="check_out" id="check_out" placeholder="Check out" class="flatpickr-reservation" value="{{$r_list->check_out }}" noRequired />
                                                    <x-select xModel="at" name="accommodation_type" id="accommodation_type" placeholder="Accommodation Type" :value="$arrAccType" :title="$arrAccTypeTitle" selected="{{$r_list->accommodation_type}}" noRequired />
                                                    {{-- Number of Guest --}}
                                                    <x-input type="number" name="pax" id="pax" placeholder="Number of Guests" value="{{$r_list->pax}}" noRequired />
                                                    <template x-if="at === 'Day Tour' || at === 'Overnight'">
                                                        <x-tooltip title="If you want to make changes, you can do so in the tour information." color="info">
                                                            <x-input type="number" name="tour_pax" id="tour_pax" placeholder="How many people will be going on the tour" value="{{$r_list->tour_pax?? ''}}" disabled noRequired />
                                                        </x-tooltip>
                                                    </template>
                                                    {{-- Payment Method  --}}
                                                    <x-select id="payment_method" name="payment_method" placeholder="Payment Method" :value="$arrPayment"  :title="$arrPayment" selected="{{$r_list->payment_method}}" noRequired />
                                                    <div class="modal-action">
                                                        <button class="btn btn-primary">Save</button>
                                                    </div>
                                                </div>
                        
                                              </div>
                                          </form>
                                    </x-modal>
                                @endif
                            </div>
                        
                        <div class="overflow-x-auto">
                            <table class="table">
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
                                    <table class="table table-zebra table-xs md:table-md">
                                    <!-- head -->
                                    <thead>
                                        <tr class="text-neutral font-bold">
                                            <td class="flex items-center gap-4">
                                                <h2 class="font-bold ">Tour</h2>
                                                <a href="{{route('user.reservation.edit.tour', ['id' => encrypt($r_list->id), 'gpax='.$r_list->tour_pax])}}" class="btn btn-sm btn-ghost btn-circle btn-primary {{$r_list->status > 1 ? 'btn-disabled' : ''}}">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                            </td>
                                            <td>Price</td>
                                            <td>Quantity</td>
                                            <td>Amount</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $amount = 0; @endphp
                                        @foreach ($menu as $key => $item)
                                            <tr>
                                                <td>{{$item['title']}}</td> 
                                                <td>₱ {{number_format($item['price'], 2)}}</td> 
                                                <td class="text-center">{{$item['tpx']}} guest</td> 
                                                <td>₱ {{number_format($item['amount'], 2)}}</td> 
                                            </tr>
                                            @php $amount += $item['amount']; @endphp

                                        @endforeach
                                        <tr class="font-bold">
                                            <td></td> 
                                            <td></td> 
                                            <td class="text-right">Total</td> 
                                            <td >₱ {{number_format($amount , 2)}}</td> 
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
                        <h1 class="my-1 text-xl "><strong>Additional Request: </strong></h1>
                        <div class="my-5 w-full">
                            <div class="w-auto">
                                <div class="overflow-x-auto">
                                    <table class="table table-zebra table-sm">
                                    <!-- head -->
                                    <thead>
                                        <tr>
                                            <th>Addons</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $totalAddons = 0 @endphp
                                        @foreach ($other_addons as $key => $addon)
                                            <tr>
                                                <td>{{$addon['title']}}</td> 
                                                <td>{{$addon['pcs'] ?? 0}}</td> 
                                                <td>₱ {{number_format($addon['price'], 2)}}</td> 
                                                <td>₱ {{number_format($addon['amount'], 2)}}</td> 
                                                @php $totalAddons += $addon['amount'] @endphp
                                            </tr>
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
                        </div>
                    </article>
                @endif
                <div class="divider"></div>
                    <article class="text-md tracking-tight text-neutral my-5 w-auto">
                        <h2 class="text-xl md:text-2xl mt-5 font-bold">Transaction</h2>
                        <div class="overflow-x-auto">
                            <table class="table">
                                <thead>
                                    <th></th>
                                    <th></th>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th>Service Cost:</th>
                                        <td>₱ {{ number_format((double)$r_list->getServiceTotal() ?? 0, 2) }}</td>
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
                                            <th>Total of Room Rate</th>
                                            <td>₱ {{ number_format($rate['orig_amount'] ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Senior Guest</th>
                                            <td>₱ {{$dscPerson ?? 0}} Guest</td>
                                        </tr>
                                        <tr>
                                            <th>Discount</th>
                                            <td>20%</td>
                                        </tr>
                                        <tr>
                                            <th>Total of Room Rate Discounted</th>
                                            <td>₱ {{ number_format($rate['amount'], 2) }}</td>
                                        </tr>
                                    @endif
                                    @if($r_list->status > 0 && $r_list->status < 4 && $dscPerson === 0)
                                        <tr>
                                            <th>Room Rate:</th>
                                            <td>{{$rate['name'] ?? ''}} -  ₱ {{ number_format((double)$rate['price'] ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <th >No. of days: </th>
                                            <td>{{$r_list->getNoDays() > 1 ? $r_list->getNoDays() . ' days' : $r_list->getNoDays() . ' day'}}</td>
                                        </tr>
                                        <tr>
                                            <th>Total of Room Rate: </th>
                                            <td>₱ {{ number_format($rate['amount'], 2) }}</td>
                                        </tr>
                                    @endif
                                <tr>
                                    <th>Total Cost: </th>
                                    <td>₱ {{ number_format($r_list->getTotal(), 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Downpayment: </th>
                                    <td>{{$r_list->downpayment() !== 0 ? '₱ ' . number_format($r_list->downpayment(), 2) : 'No Downpayment'}}</td>
                                </tr>
                                <tr>
                                    <th>Payment after Check-in: </th>
                                    <td>{{$r_list->checkInPayment() !== 0 ? '₱ ' . number_format($r_list->checkInPayment(), 2) : 'No Payment' }}</td>
                                </tr>
                                <tr>
                                    <th>Payment after Check-out: </th>
                                    <td>{{$r_list->checkOutPayment() !== 0 ? '₱ ' . number_format($r_list->checkOutPayment(), 2) : 'No Payment' }}</td>
                                </tr>
                                @if($r_list->checkOutPayment() === 0)
                                    <tr class="text-md tracking-tight text-neutral my-5">
                                        <th>Balance due: </th>
                                        <td>{{$r_list->balance() !== 0 ? '₱ ' . number_format($r_list->balance(), 2) : 'No Balance' }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </article>
                
                <div class="divider"></div>
                <div x-data="{show: false}" class=" w-full">
                    <div class="flex items-start space-x-2">
                        <h2 class="text-xl md:text-2xl mb-5 font-bold">Valid ID</h2> 
                        <div class="join">
                            <button @click="show = !show" type="button" x-text="show ? 'Hide' : 'Show' " class="btn btn-sm join-item"></button>
                            <label for="edit_id_modal" class="btn btn-primary btn-sm join-item" {{$r_list->status > 0 ? 'disabled' : ''}}>Change</label> 
                        </div>
                        <x-modal id="edit_id_modal" title="Change Valid ID" loader>
                            <form action="{{route('profile.update.validid', encrypt($r_list->userreservation->id))}}" method="POST" enctype="multipart/form-data" >
                                @csrf
                                @method('PUT')
                                <x-drag-drop name="valid_id" id="valid_id" />
                                <div class="modal-action">
                                    <button type="submit" class="btn btn-primary" @click="loader = true">Save</button>
                                </div>
                            </form>
                        </x-modal>
                    </div>
                    <template x-if="show">
                        <div class="w-full rounded flex justify-center">
                            <img src="{{route('private.image', ['folder' => explode('/', $r_list->userReservation->valid_id)[0], 'filename' => explode('/', $r_list->userReservation->valid_id)[1]])}}" alt="Valid ID of {{$r_list->userReservation->name()}}">
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