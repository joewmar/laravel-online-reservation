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

@endphp

<x-system-layout :activeSb="$activeSb">
    <x-system-content title="" back=true>
        {{-- User Details --}}
       <div class="px-0 md:px-20">
        <div class="w-full sm:flex sm:space-x-6">
            <div class="flex-shrink-0 mb-6 h-15 sm:h-32 w-15 sm:w-32 sm:mb-0">
                @if(filter_var($r_list->userReservation->avatar ?? '', FILTER_VALIDATE_URL))
                    <img src="{{$r_list->userReservation->avatar}}" alt="" class="object-cover object-center w-full h-full rounded">
                @elseif($r_list->userReservation->avatar ?? false)
                    <img src="{{asset('storage/'. $r_list->userReservation->avatar)}}" alt="" class="object-cover object-center w-full h-full rounded">
                @else
                    <img src="{{asset('images/avatars/no-avatar.png')}}" alt="" class="object-cover object-center w-full h-full rounded">
                @endif
            </div>            
            <div class="flex flex-col space-y-4">
                <div>
                    <h2 class="text-2xl font-semibold">{{$r_list->userReservation->name()}}</h2>
                    <span class="block text-sm text-neutral">{{$r_list->userReservation->age()}} years old from {{$r_list->userReservation->country}}</span>
                    <span class="text-sm text-neutral">{{$r_list->userReservation->nationality}}</span>
                </div>
                <div class="space-y-1">
                    <span class="flex items-center space-x-2">
                        <i class="fa-regular fa-envelope w-4 h-4"></i>
                        <span class="text-neutral">{{$r_list->userReservation->email}}</span>
                    </span>
                    <span class="flex items-center space-x-2">
                        <i class="fa-solid fa-phone w-4 h-4"></i>
                        <span class="text-neutral">{{$r_list->userReservation->contact}}</span>
                    </span>
                </div>
            </div>
        </div>
        <div class="flex md:hidden justify-end">
            <div class="dropdown dropdown-top dropdown-end">
                <label tabindex="0" class="btn btn-ghost btn-circle">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-5 h-5 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"></path></svg>
                </label>
                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                    @if($r_list->status >= 1 && $r_list->status < 2)
                        <li>
                            <a href="{{route('system.reservation.show.online.payment', encrypt($r_list->id))}}" class="btn btn-ghost btn-sm">
                                Online Payment
                            </a>
                        </li>
                    @endif
                    @if($r_list->status > 1 && $r_list->status < 3)
                        <li>
                            <a href="{{route('system.reservation.show.extend', encrypt($r_list->id))}}" class="btn btn-ghost btn-sm">
                                Extend Room Stay
                            </a>
                        </li>
                        <li>
                            <a href="{{route('system.reservation.show.addons', encrypt($r_list->id))}}" class="btn btn-ghost btn-sm">
                                Add-ons
                            </a>
                        </li>
                    @endif
                    @if($r_list->status >= 1)
                        <li>
                            <a href="{{route('reservation.receipt', encrypt($r_list->id))}}" class="btn btn-ghost btn-sm">
                                Reciept
                            </a>
                        </li>
                    @endif
                        <li>
                            <a href="{{route('system.reservation.edit', encrypt($r_list->id))}}" class="btn btn-ghost btn-sm">
                                Edit Information
                            </a>
                        </li>
                    </ul>
              </div>
        </div>
        <div class="w-full hidden md:flex justify-end space-x-1">
            @if($r_list->status >= 1 && $r_list->status < 2)
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
            @if($r_list->status >= 1)
                <a href="{{route('reservation.receipt', encrypt($r_list->id))}}" class="btn btn-success btn-sm">
                    <i class="fa-solid fa-receipt"></i>
                    Reciept
                </a>
            @endif
            @if ($r_list->status === 3)
            <a href="{{route('system.reservation.edit', encrypt($r_list->id))}}" class="btn btn-ghost btn-sm" disabled>
                Edit Information
            </a>
            @else
            <a href="{{route('system.reservation.edit', encrypt($r_list->id))}}" class="btn btn-ghost btn-sm">
                Edit Information
            </a>
            @endif
        </div>
        <div class="divider"></div>
        <div class="block">
            <article class="text-md tracking-tight text-neutral my-5 w-auto">
                <h2 class="text-2xl mb-5 font-bold">Details</h2>
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
                @if($r_list->accommodation_type !== 'Room Only')
                    <div class="w-auto">
                        <div class="overflow-x-auto">
                            <table class="table table-zebra">
                            <!-- head -->
                            <thead>
                                <tr class="text-neutral font-bold">
                                    <td>Tour</td>
                                    <td>Price</td>
                                    <td>Amount</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($menu as $key => $item)
                                    <tr>
                                        <td>{{$item['title']}}</td> 
                                        <td>₱ {{number_format($item['price'], 2)}}</td> 
                                        <td>₱ {{number_format($item['amount'], 2)}}</td> 
                                    </tr>
                                @endforeach
                            </tbody>
                            </table>
                        </div>
                    </div>
                @endif
                {{-- @if($r_list->status < 7)
                    <article class="mt-3 flex flex-col items-end">
                        <div>
                            <span class="font-medium">Total Cost: </span>₱ {{ number_format($r_list->getTotal(), 2) }}
                        </div>
                        <div class="text-md tracking-tight text-neutral">
                            <span class="font-medium">Downpayment: </span>₱ {{ number_format($r_list->downpayment() ?? 0, 2) }}
                        </div>
                        <div class="text-md tracking-tight text-neutral my-5">
                            @php $balance = abs($total - $downpayment); @endphp
                            <span class="font-medium">Balance due: </span>₱ {{ number_format($r_list->balance() ?? 0, 2) }}
                        </div>
                    </article>
                @endif --}}
            </article>
        </div>
        @if(!empty($tour_addons) || !empty($other_addons))
            <div class="divider"></div>
            <article>
                <h1 class="my-1 text-xl "><strong>Additional Request: </strong></h1>
                @if(!empty($tour_addons))
                    <div class="my-5 w-96">
                        <p class="my-1 font-medium">Additional Tour: </p>
                        <div class="w-auto">
                            <div class="overflow-x-auto">
                                <table class="table table-zebra">
                                <!-- head -->
                                <thead>
                                    <tr>
                                        <th>Tour</th>
                                        <th>Price</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tour_addons as $key => $tour)
                                        <tr>
                                            <td>{{$tour['title']}}</td> 
                                            <td>₱ {{number_format($tour['price'], 2)}}</td> 
                                            <td>₱ {{number_format($tour['amount'], 2)}}</td> 
                                        </tr>
                                    @endforeach
                                </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
                @if(!empty($other_addons))
                    <div class="my-5 w-96">
                        <p class="my-1 font-medium">Other:</p>
                        <div class="w-auto">
                            <div class="overflow-x-auto">
                                <table class="table table-zebra">
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
                                    @foreach ($other_addons as $key => $addon)
                                        <tr>
                                            <td>{{$addon['title']}}</td> 
                                            <td>{{$addon['pcs'] ?? 0}} pcs</td> 
                                            <td>₱ {{number_format($addon['price'], 2)}}</td> 
                                            <td>₱ {{number_format($addon['amount'], 2)}}</td> 
                                        </tr>
                                    @endforeach
                                </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </article>
        @endif
        <div class="divider"></div>
            <article class="text-md tracking-tight text-neutral my-5 w-auto">
                <h2 class="text-2xl mt-5 font-bold">Transaction</h2>
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
                <h2 class="text-2xl mb-5 font-bold">Valid ID</h2> 
                <button @click="show = !show" type="button" x-text="show ? 'Hide' : 'Show' " class="btn btn-primary btn-sm"></button>
            </div>
            <template x-if="show">
                <div class="w-full rounded flex justify-center">
                    <img src="{{route('private.image', ['folder' => explode('/', $r_list->userReservation->valid_id)[0], 'filename' => explode('/', $r_list->userReservation->valid_id)[1]])}}" alt="Valid ID of {{$r_list->userReservation->name()}}">
                </div>
            </template>
        </div>
        <div class="divider"></div>
        <div class="w-full">
            <h2 class="text-2xl mb-5 font-bold">Messages</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5"> 
                <div class="bg-base-100 shadow-lg p-6 rounded">
                    <h2 class="text-lg font-bold">Request Message</h2>
                    <p class="text-md">{{$r_list->message['request'] ?? 'None'}}</p>
                </div>
                <div class="bg-base-100 shadow-lg p-6 rounded">
                    <h2 class="text-lg font-bold">Reason to Cancel</h2>
                    <p class="text-md">{{$r_list->message['cancel']['message'] ?? 'None'}}</p>
                </div>
                <div class="bg-base-100 shadow-lg p-6 rounded">
                    <h2 class="text-lg font-bold">Reason to Reschedule</h2>
                    <p class="text-md">{{$r_list->message['reschedule']['message'] ?? 'None'}}</p>
                </div>
            </div>
        </div>
        <div class="divider"></div>
        @if($r_list->status >= 0 && $r_list->status < 1 && $r_list->user_id)
            <article class="text-md tracking-tight text-neutral my-5 w-auto">
                <h2 class="text-2xl mb-5 font-bold">Conflict Schedule of {{$r_list->userReservation->name()}}</h2>
                <div class="overflow-x-auto w-full">
                    <table class="table w-full">
                        <!-- head -->
                        <thead>
                            <tr>
                                <th></th>
                                <th>Name</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Action</th>
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
                                        <a href="{{route('system.reservation.show', encrypt($list->id))}}" class="btn btn-info btn-xs" >View</a>
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
        <div class="flex justify-end space-x-1">
            @props(['data'])
                <div class="join">
                    <a href="{{route('system.reservation.show.rooms', encrypt($r_list->id))}}" class="btn btn-secondary btn-xs join-item" {{!($r_list->status == 0) ? 'disabled' : ''}}>Confirm</a>
                    <label for="checkin" class="btn btn-success btn-xs join-item" {{!($r_list->status == 1) ? 'disabled' : ''}}>Check-in</label>
                    <x-checkin name="{{$r_list->userReservation->name() ?? ''}}" :datas="$r_list" />
                    <label for="checkout" class="btn btn-warning btn-xs join-item" {{!($r_list->status == 2) ? 'disabled' : ''}}>Check-out</label>
                    <x-checkout name="{{$r_list->userReservation->name() ?? ''}}" :datas="$r_list" />
                    <a href="{{route('system.reservation.show.cancel', encrypt($r_list->id))}}" class="btn btn-xs btn-error join-item" {{$r_list->status >= 2 ? 'disabled' : ''}}>Cancel</a>
                    <a href="{{route('system.reservation.show.reschedule', encrypt($r_list->id))}}" class="btn btn-xs btn-accent join-item" {{$r_list->status >= 2 ? 'disabled' : ''}}>Reschedule</a>
                </div>
        </div>
       </div>
    </x-system-content>
</x-system-layout>