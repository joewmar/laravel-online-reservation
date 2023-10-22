@php
    $downpayment = 0;
    $dscPerson = 0;

    foreach ($r_list->transaction ?? [] as $outerKey => $outerItem) {
        if ($outerKey == 'payment' && is_array($outerItem)) {
            $downpayment = $outerItem['downpayment'] ?? 0;
            $dscPerson = $outerItem['discountPerson'] ?? 0;
            continue;
        }
    }
    $tours = [];
    foreach ($menu as $key => $value) {
        $tours[$key] = $value;
    }
    foreach ($tour_addons as $key => $value) {
        $tours[$key] = $value;
    }

@endphp

<x-system-layout :activeSb="$activeSb">
    <x-system-content title="" back="{{route('system.reservation.home')}}">
        {{-- User Details --}}
       <div x-data="{loader : true}" class="px-3 md:px-20">
        <x-profile :rlist="$r_list" />
        @if (!((auth('system')->user()->type == 2)))
            <div class="flex md:hidden justify-end">
                <div class="dropdown dropdown-top dropdown-end">
                    <label tabindex="0" class="btn btn-ghost btn-circle">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-5 h-5 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"></path></svg>
                    </label>
                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                        @if($r_list->status >= 1 && $r_list->status < 3)
                            <li>
                                <a href="{{route('system.reservation.show.online.payment', encrypt($r_list->id))}}">
                                    Online Payment
                                </a>
                            </li>
                        @endif
                        @if($r_list->status > 1 && $r_list->status <= 3)
                            <li>
                                <a href="{{route('system.reservation.show.extend', encrypt($r_list->id))}}">
                                    Extend Room Stay
                                </a>
                            </li>
                            <li>
                                <a href="{{route('system.reservation.show.addons', encrypt($r_list->id))}}">
                                    Add-ons
                                </a>
                            </li>
                        @endif
                        @if($r_list->status >= 1)
                            @if(!$r_list->status == 3 )
                                <li>
                                    <a href="{{route('reservation.receipt', encrypt($r_list->id))}}">
                                        Reciept
                                    </a>
                                </li>
                            @endif
                        @endif
                            <li>
                                <label for="edit_modal" {{$r_list->status === 3 ? 'disabled' : ''}}>
                                    Edit
                                </label>
                            </li>
                        </ul>
                </div>
            </div>
            <div class="w-full hidden md:flex justify-end space-x-1">
                @if($r_list->status >= 1 && $r_list->status < 3)
                    <a href="{{route('system.reservation.show.online.payment', encrypt($r_list->id))}}" class="btn btn-info btn-sm">
                        <i class="fa-solid fa-credit-card"></i>
                        Online Payment
                    </a>
                @endif
                @if($r_list->status > 1 && $r_list->status < 3)
                    <a href="{{route('system.reservation.show.extend', encrypt($r_list->id))}}" class="btn btn-accent btn-sm">
                        <i class="fa-solid fa-circle-plus"></i>                   
                        Extend Room Stay
                    </a>
                    <a href="{{route('system.reservation.show.addons', encrypt($r_list->id))}}" class="btn btn-accent btn-sm">
                        Add-ons
                    </a>
                @endif
                @if($r_list->status == 3)
                    <a href="{{route('reservation.receipt', encrypt($r_list->id))}}" class="btn btn-success btn-sm">
                        <i class="fa-solid fa-receipt"></i>
                        Reciept
                    </a>
                @endif

                <label for="edit_modal" class="btn btn-sm" {{$r_list->status === 3 ? 'disabled' : ''}}>
                    Edit
                </label>
            </div>
        @else
            <div class="flex md:hidden justify-end">
                <div class="dropdown dropdown-top dropdown-end">
                    <label tabindex="0" class="btn btn-ghost btn-circle">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-5 h-5 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"></path></svg>
                    </label>
                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                        @if($r_list->status > 1 && $r_list->status < 3)
                            <li>
                                <a href="{{route('system.reservation.show.extend', encrypt($r_list->id))}}">
                                    <i class="fa-solid fa-circle-plus"></i>                   
                                    Extend Room Stay
                                </a>
                            </li>
                            <li>
                                <a href="{{route('system.reservation.show.addons', encrypt($r_list->id))}}">
                                    Add-ons
                                </a>
                            </li>
                            <li>
                                <a href="{{route('system.reservation.edit.rooms', encrypt($r_list->id))}}">
                                    Edit Room Assign
                                </a>
                            </li>
                            @if($r_list->status == 3)
                                <li>
                                    <a href="{{route('reservation.receipt', encrypt($r_list->id))}}">
                                        <i class="fa-solid fa-receipt"></i>
                                        Reciept
                                    </a>
                                </li>
                            @endif
                        @endif
                    </ul>
                </div>
            </div>
            <div class="w-full hidden md:flex justify-end space-x-1">
                @if($r_list->status > 1 && $r_list->status < 3)
                    <a href="{{route('system.reservation.show.extend', encrypt($r_list->id))}}" class="btn btn-accent btn-sm">
                        <i class="fa-solid fa-circle-plus"></i>                   
                        Extend Room Stay
                    </a>
                    <a href="{{route('system.reservation.show.addons', encrypt($r_list->id))}}" class="btn btn-accent btn-sm">
                        Add-ons
                    </a>
                    <a href="{{route('system.reservation.edit.rooms', encrypt($r_list->id))}}" class="btn btn-warning btn-sm">
                        Edit Room Assign
                    </a>
                    @if($r_list->status >= 1)
                        @if(!$r_list->status == 6 )
                            <a href="{{route('reservation.receipt', encrypt($r_list->id))}}" class="btn btn-success btn-sm">
                                <i class="fa-solid fa-receipt"></i>
                                Reciept
                            </a>
                        @endif
                    @endif
                @endif
            </div>
        @endif
        <x-modal title="What kind do you want to edit?" id="edit_modal">
            <div class="grid grid-cols-3 gap-5">
                <a href="{{route('system.reservation.edit.information', encrypt($r_list->id))}}" class="card border hover:bg-primary hover:text-primary-content">
                    <div class="card-body justify-center items-center">
                      <h2 class="card-title">Information</h2>
                    </div>
                </a>
                <a href="{{route('system.reservation.edit.rooms', encrypt($r_list->id))}}" class="card border hover:bg-primary hover:text-primary-content">
                    <div class="card-body justify-center items-center">
                      <h2 class="card-title">Room</h2>
                    </div>
                </a>
                <a href="{{route('system.reservation.edit.tour', encrypt($r_list->id))}}" class="card border hover:bg-primary hover:text-primary-content">
                    <div class="card-body justify-center items-center">
                      <h2 class="card-title">Services</h2>
                    </div>
                </a>
            </div>
        </x-modal>
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
                @if(!empty($tours))
                    <div class="w-auto">
                        <div class="overflow-x-auto">
                            <table class="table table-xs md:table-sm w-full">
                            <!-- head -->
                            <thead>
                                <tr class="text-neutral font-bold">
                                    <td>Tours</td>
                                    <td>Price</td>
                                    <td>Quantity</td>
                                    <td>Amount</td>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalTour = 0 @endphp
                                @foreach ($tours as $key => $item)
                                    <tr>
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
                                    <td>{{$dscPerson ?? 0}} Guest</td>
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
                            <td class="flex items-center gap-4">
                                {{$r_list->downpayment() !== 0 ? '₱ ' . number_format($r_list->downpayment(), 2) : 'No Downpayment'}}
                                @if ($r_list->downpayment() !== 0)
                                    <a href="{{route('system.reservation.edit.payment', encrypt($r_list->id))}}" class="btn btn-sm btn-ghost btn-circle btn-primary {{$r_list->status >= 1 && $r_list->status <= 2 ? '' : 'btn-disabled'}}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a> 
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Payment after Check-in: </th>
                            <td class="flex items-center gap-4">
                                {{$r_list->checkInPayment() !== 0 ? '₱ ' . number_format($r_list->checkInPayment(), 2) : 'No Payment' }}
                                @if ($r_list->checkInPayment() != 0)
                                    <a href="{{route('system.reservation.edit.payment', ['id' => encrypt($r_list->id), 'tab=CINP'])}}" class="btn btn-sm btn-ghost btn-circle btn-primary {{$r_list->status === 2 ? '' : 'btn-disabled'}}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a> 
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Payment after Check-out: </th>
                            <td>{{$r_list->checkOutPayment() !== 0 ? '₱ ' . number_format($r_list->checkOutPayment(), 2) : 'No Payment' }}</td>
                        </tr>
                        <tr >
                            <th>Balance due: </th>
                            <td>{{$r_list->balance() !== 0 ? '₱ ' . number_format($r_list->balance(), 2) : 'No Balance' }}</td>
                        </tr>
                        <tr >
                            <th>Refund: </th>
                            <td>{{$r_list->refund() !== 0 ? '₱ ' . number_format($r_list->refund(), 2) : 'No Refund' }}</td>
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
        <div class="w-full">
            <h2 class="text-xl md:text-2xl mb-5 font-bold">Messages</h2>
            <div class="flow-root p-3">
                <dl class="-my-3 divide-y divide-gray-100 text-sm">
                  <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="font-medium text-gray-900">Request Message</dt>
                    <dd class="text-gray-700 sm:col-span-2">{{$r_list->message['request'] ?? 'None'}}</dd>
                  </div>
              
                  <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="font-medium text-gray-900">Reason to Cancel</dt>
                    <dd class="text-gray-700 sm:col-span-2">{{$r_list->message['cancel']['message'] ?? 'None'}}</dd>
                  </div>
              
                  <div class="grid grid-cols-1 gap-1 py-3 sm:grid-cols-3 sm:gap-4">
                    <dt class="font-medium text-gray-900">Reason to Reschedule</dt>
                    <dd class="text-gray-700 sm:col-span-2">{{$r_list->message['reschedule']['message'] ?? 'None'}}</dd>
                  </div>
                </dl>
              </div>
        
        </div>
        <div class="divider"></div>
        @if($r_list->status >= 0 && $r_list->status < 1 && $r_list->user_id)
            <article class="tracking-tight text-neutral my-5 w-auto">
                <h2 class="text-xl md:text-2xl mb-5 font-bold">Conflict Schedule of {{$r_list->userReservation->name()}}</h2>
                <div class="overflow-x-auto w-full">
                    <table class="table table-xs md:table-sm w-full">
                        <!-- head -->
                        <thead>
                            <tr>
                                <th></th>
                                <th>Name</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <!-- row 1 -->
                            @forelse ($conflict as $list)
                                <tr>
                                    <td>
                                        <div class="flex items-center space-x-3">
                                            <div class="avatar">
                                                <div class="mask mask-squircle w-12 h-12">
                                                    <img src="{{$list->userReservation->avatar ? asset('storage/'.$list->userReservation->avatar) : asset('images/avatars/no-avatar.png')}}" />
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                    <div>
                                        <div class="font-bold">{{$list->userReservation->name() ?? ''}}</div>
                                        <div class="text-sm opacity-50">{{$list->userReservation->country}}</div>
                                    </div>
                                    </td>
                                    <td class="font-bold">{{ \Carbon\Carbon::parse($list->check_in)->format('F j, Y')}}</td>
                                    <td class="font-bold">{{ \Carbon\Carbon::parse($list->check_out)->format('F j, Y')}}</td>
                                    <td>{{$list->status()}}</td>
                                    <td>{{ \Carbon\Carbon::parse($list->created_at)->format('M j, Y g:i A')}}</td>

                                    <th class="w-auto">
                                        <a href="{{route('system.reservation.show', encrypt($list->id))}}" class="btn btn-ghost btn-sm btn-circle hover:btn-primary" >
                                            <i class="fa-solid fa-ellipsis"></i>
                                        </a>
                                    </th>
                                
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center font-bold">No Conflict</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </article>
        @endif
        <div class="flex justify-end">
            <div class="join hidden md:inline">
                @if(!(auth('system')->user()->type == 2))
                    <a href="{{route('system.reservation.show.rooms', encrypt($r_list->id))}}" class="btn btn-secondary btn-xs join-item {{!($r_list->status >= 0 && $r_list->status <= 0)  ? 'btn-disabled' : ''}}">Confirm</a>
                @endif
                <label for="checkin" class="btn btn-success btn-xs join-item" {{$r_list->status() == "Confirmed" ? '' : 'disabled'}}>Check-in</label>
                <label for="checkout" class="btn btn-info btn-xs join-item" {{$r_list->status() == "Check-in" ? '' : 'disabled'}}>Check-out</label>
                @if($r_list->status() == "Confirmed")
                    <x-checkin name="{{$r_list->userReservation->name() ?? ''}}" :datas="$r_list" />
                @elseif($r_list->status() == "Check-in")
                    <x-checkout name="{{$r_list->userReservation->name() ?? ''}}" :datas="$r_list" />
                @endif
                @if(!(auth('system')->user()->type == 2))
                    <a href="{{route('system.reservation.show.cancel', encrypt($r_list->id))}}" class="btn btn-xs btn-error join-item {{$r_list->status === 2 || $r_list->status === 3 || $r_list->status === 5 || $r_list->status === 6 ? 'btn-disabled' : ''}}" >Cancel</a>
                    <a href="{{route('system.reservation.show.reschedule', encrypt($r_list->id))}}" class="btn btn-xs btn-accent join-item {{$r_list->status === 2 || $r_list->status === 3 || $r_list->status === 5 || $r_list->status === 6 ? 'btn-disabled' : ''}}">Reschedule</a>
                @endif
            </div>
            <div class="join inline md:hidden">
                @if(!(auth('system')->user()->type == 2))
                    <a href="{{route('system.reservation.show.rooms', encrypt($r_list->id))}}" class="btn btn-secondary btn-xs join-item {{!($r_list->status >= 0 && $r_list->status <= 0) ? 'btn-disabled' : ''}}">Confirm</a>
                @endif
                <label for="checkin" class="btn btn-success btn-xs join-item" {{$r_list->status() == "Confirmed" ? '' : 'disabled'}}>Check-in</label>
                <label for="checkout" class="btn btn-info btn-xs join-item" {{$r_list->status() == "Check-in" ? '' : 'disabled'}}>Check-out</label>
                @if($r_list->status() == "Confirmed")
                    <x-checkin name="{{$r_list->userReservation->name() ?? ''}}" :datas="$r_list" />
                @elseif($r_list->status() == "Check-in")
                    <x-checkout name="{{$r_list->userReservation->name() ?? ''}}" :datas="$r_list" />
                @endif
                @if(!(auth('system')->user()->type == 2))
                    <div class="dropdown dropdown-left dropdown-end">
                        <label tabindex="0" class="btn join-item btn-xs">                                                        
                            <i class="fa-solid fa-ellipsis"></i>
                        </label>
                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                            <li><a href="{{$r_list->status === 2 || $r_list->status === 3 || $r_list->status === 5 || $r_list->status === 6 ? '#' : route('system.reservation.show.cancel', encrypt($r_list->id))}}" class="text-error">Cancel</a></li>
                            <li><a href="{{$r_list->status === 2 || $r_list->status === 3 || $r_list->status === 4 || $r_list->status == 6  || $r_list->status !== 7  ? '#' : route('system.reservation.show.reschedule', encrypt($r_list->id))}}" class="text-accent-content" >Reschedule</a></li>
                        </ul>
                    </div>
                @endif
            </div>
        </div>
       </div>
    </x-system-content>
</x-system-layout>