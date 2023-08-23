@php
    $total = 0;
    $downpayment = 0;
    $dscPerson = null;

    foreach ($r_list->transaction as $outerKey => $outerItem) {
        if ($outerKey == 'payment' && is_array($outerItem)) {
            $downpayment = $outerItem['downpayment'] ?? 0;
            $dscPerson = $outerItem['discountPerson'] ?? 0;
            continue;
        }
    }

@endphp

<x-landing-layout>
    <x-navbar :activeNav="$activeNav" type="plain"/>
    <x-full-content>
        <section class="p-6 pt-24">
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
                            @if($r_list->status >= 1 && $r_list->status !== 5)
                                <li>
                                    <a href="{{route('reservation.receipt', encrypt($r_list->id))}}" class="btn btn-ghost btn-sm">
                                        Reciept
                                    </a>
                                </li>
                            @endif
                                <li>
                                    <a href="{{route('user.reservation.edit', encrypt($r_list->id))}}" class="btn btn-ghost btn-sm" {{$r_list->status >= 0 || $r_list->status !== 5 ? 'disabled' : ''}}>
                                        Edit Information
                                    </a>
                                </li>
                            </ul>
                      </div>
                </div>
                <div class="w-full hidden md:flex justify-end space-x-1">
                    @if($r_list->status >= 1 && $r_list->status !== 5)
                        <a href="{{route('reservation.receipt', encrypt($r_list->id))}}" class="btn btn-success btn-sm">
                            <i class="fa-solid fa-receipt"></i>
                            Reciept
                        </a>
                    @endif
                    <a href="{{route('user.reservation.edit', encrypt($r_list->id))}}" class="btn btn-ghost btn-sm" {{$r_list->status >= 0 || $r_list->status !== 5 ? 'disabled' : ''}}>
                        Edit Information
                    </a>
                </div>
                <div class="divider"></div>
                <div class="block md:flex items-center justify-between">
                    <article class="text-md tracking-tight text-neutral my-5 w-auto">
                        <h2 class="text-2xl mb-5 font-bold">Details</h2>
                        <p class="my-1"><strong>Number of Guest: </strong>{{$r_list->pax . ' guest' ?? 'None'}}</p>
                        @if(!empty($r_list->tour_pax))
                            <p class="my-1"><strong>Guest going on tour: </strong>{{$r_list->tour_pax . ' guest' ?? 'None'}}</p>
                        @endif
                        <p class="my-1"><strong>Type: </strong>{{$r_list->accommodation_type ?? 'None'}}</p>
                        <p class="my-1"><strong>Room No: </strong>{{!empty($rooms) ? $rooms : 'None'}}</p>
                        <p class="my-1"><strong>Check-in: </strong>{{ \Carbon\Carbon::createFromFormat('Y-m-d', $r_list->check_in )->format('l, F j, Y') ?? 'None'}}</p>
                        <p class="my-1"><strong>Check-out: </strong>{{ \Carbon\Carbon::createFromFormat('Y-m-d', $r_list->check_out )->format('l, F j, Y') ?? 'None'}}</p>
                        <p class="my-1"><strong>Payment Method: </strong>{{ $r_list->payment_method ?? 'None'}}</p>
                        <p class="my-1"><strong>Status: </strong>{{ $r_list->status() ?? 'None'}}</p>
                    </article>
                    <article>
                        @if($r_list->accommodation_type !== 'Room Only')
                            <div class="w-auto">
                                <div class="overflow-x-auto">
                                    <table class="table table-zebra table-xs">
                                    <!-- head -->
                                    <thead>
                                        <tr>
                                            <th>Tour</th>
                                            <th>Price</th>
                                            <th>Amount</th>
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
                        @if($r_list->status > 4 && $r_list->status < 7)
                            <article class="mt-3">
                                <p class="text-md tracking-tight text-neutral">
                                    <span class="font-medium">Total Cost: </span>₱ {{ number_format($r_list->getTotal(), 2) }}
                                </p>
                                <p class="text-md tracking-tight text-neutral">
                                    <span class="font-medium">Downpayment: </span>₱ {{ number_format($r_list->downpayment() ?? 0, 2) }}
                                </p>
                                <p class="text-md tracking-tight text-neutral my-5">
                                    @php $balance = abs($total - $downpayment); @endphp
                                    <span class="font-medium">Balance due: </span>₱ {{ number_format($r_list->balance() ?? 0, 2) }}
                                </p>
                            </article>
                        @endif
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
                                        <table class="table table-zebra table-xs">
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
                                        <table class="table table-zebra table-xs">
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
                @if($r_list->status > 0 && $r_list->status < 4)
                    <div class="divider"></div>
                        <article class="text-md tracking-tight text-neutral my-5 p-5 w-auto">
                        @if (isset($dscPerson))
                            <p class="text-md tracking-tight text-neutral">
                                <span class="font-medium">Room Rate (Orginal Price): </span>{{$rate['name'] ?? ''}} -  ₱ {{ number_format((double)$rate['price'] ?? 0, 2) }}
                            </p>
                            <p class="text-md tracking-tight text-neutral">
                                <span class="font-medium">Total of Room Rate: </span>₱ {{ number_format($rate['orig_amount'] ?? 0, 2) }}
                            </p>
                            <p class="text-md tracking-tight text-neutral">
                                <span class="font-medium">Senior Guest : </span>{{$dscPerson ?? 0}} Guest
                            </p>
                            <p class="text-md tracking-tight text-neutral">
                                <span class="font-medium">Discount : </span>20%
                            </p>
                            <p class="text-md tracking-tight text-neutral">
                                <span class="font-medium">Total of Room Rate Discounted: </span>₱ {{ number_format($rate['amount'], 2) }}
                            </p>
                        @else          
                            <p class="text-md tracking-tight text-neutral">
                                <span class="font-medium">Room Rate: </span>{{$rate['name'] ?? ''}} -  ₱ {{ number_format((double)$rate['price'] ?? 0, 2) }}
                            </p>
                            <p class="text-md tracking-tight text-neutral">
                                <span class="font-medium">No. of days: </span>{{$r_list->getNoDays() > 1 ? $r_list->getNoDays() . ' days' : $r_list->getNoDays() . ' day'}}
                            </p>
                            <p class="text-md tracking-tight text-neutral">
                                <span class="font-medium">Total of Room Rate: </span>₱ {{ number_format($rate['amount'], 2) }}
                            </p>
                        @endif
                        <p class="text-md tracking-tight text-neutral">
                            <span class="font-medium">Total Cost: </span>₱ {{ number_format($r_list->getTotal(), 2) }}
                        </p>
                        <p class="text-md tracking-tight text-neutral">
                            <span class="font-medium">Downpayment: </span>{{$r_list->downpayment() !== 0 ? '₱ ' . number_format($r_list->downpayment(), 2) : 'No Downpayment'}}
                        </p>
                        <p class="text-md tracking-tight text-neutral">
                            <span class="font-medium">Payment after Check-in: </span>{{$r_list->checkInPayment() !== 0 ? '₱ ' . number_format($r_list->checkInPayment(), 2) : 'No Payment' }}
                        </p>
                        <p class="text-md tracking-tight text-neutral my-5">
                            <span class="font-medium">Balance due: </span>₱ {{ number_format($r_list->balance() ?? 0, 2) }}
                        </p>
                    </article>
                @endif
                <div class="divider"></div>
                <div class="flex flex-wrap justify-center w-full">
                    <div class="w-96 rounded">
                        <img src="{{route('private.image', ['folder' => explode('/', $r_list->valid_id)[0], 'filename' => explode('/', $r_list->valid_id)[1]])}}" alt="Valid ID of {{$r_list->userReservation->name()}}">
                    </div>
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
                            <p class="text-md">{{$r_list->message['reschedule'] ?? 'None'}}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </x-full-content>
</x-landing-layout>