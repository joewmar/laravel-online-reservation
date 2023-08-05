<x-system-layout :activeSb="$activeSb">
    <x-system-content title="">
        {{-- User Details --}}
        <div class="w-full p-8 sm:flex sm:space-x-6">
            <div class="flex-shrink-0 mb-6 h-15 sm:h-32 w-15 sm:w-32 sm:mb-0">
                @if(filter_var(auth('web')->user()->avatar ?? '', FILTER_VALIDATE_URL))
                    <img src="{{auth('web')->user()->avatar}}" alt="" class="object-cover object-center w-full h-full rounded">
                @elseif(auth('web')->user()->avatar ?? false)
                    <img src="{{asset('storage/'. auth('web')->user()->avatar)}}" alt="" class="object-cover object-center w-full h-full rounded">
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
        <div class="flex justify-end space-x-1">
            @if($r_list->status() === 'Confirmed' )
                <a href="{{route('system.reservation.show.online.payment', encrypt($r_list->id))}}" class="btn btn-info btn-sm">
                    <i class="fa-solid fa-credit-card"></i>
                    Online Payment
                </a>
            @endif
            @if($r_list->status > 1)
                <a href="{{route('reservation.receipt', encrypt($r_list->id))}}" class="btn btn-accent btn-sm">
                    <i class="fa-solid fa-circle-plus"></i>                   
                    Extend Room Stay
                </a>
                <a href="{{route('reservation.receipt', encrypt($r_list->id))}}" class="btn btn-accent btn-sm">
                    {{-- <i class="fa-solid fa-circle-plus"></i>                    --}}
                    Add-ons
                </a>
            @endif
            @if($r_list->status >= 1)
                <a href="{{route('reservation.receipt', encrypt($r_list->id))}}" class="btn btn-success btn-sm">
                    <i class="fa-solid fa-receipt"></i>
                    Reciept
                </a>
            @endif
        </div>
        <div class="divider"></div>
        @if($r_list->accommodation_type !== 'Room Only')
            <div class="block md:flex items-center justify-around">
            <article class="text-md tracking-tight text-neutral my-5 p-5 w-auto">
        @else
            <div class="block w-full">
            <article class="text-md tracking-tight text-neutral my-5 px-24 w-auto">
        @endif
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
            @if($r_list->accommodation_type !== 'Room Only')
                <div class="w-auto">
                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                        <!-- head -->
                        <thead>
                            <tr>
                                <th>Tour</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($menu as $key => $item)
                                <tr>
                                    <td>{{$item['title']}}</td> 
                                    <td>{{$item['type']}} - {{$item['pax']}} pax</td> 
                                    <td>₱ {{number_format($item['price'], 2)}}</td> 
                                    <td>₱ {{number_format($item['amount'], 2)}}</td> 
                                </tr>
                            @endforeach
                        </tbody>
                        </table>
                    </div>
                    @if($r_list->status() === 'Confirmed')
                        <p class="text-md tracking-tight text-neutral">
                            <span class="font-medium">Room Rate: </span>₱ {{ number_format( \App\Models\RoomRate::find($r_list->roomrateid)->price, 2) }}
                        </p>
                        <p class="text-md tracking-tight text-neutral">
                            <span class="font-medium">Downpayment: </span>₱ {{ number_format($r_list->downpayment ?? 0, 2) }}
                        </p>
                    @endif
                    <p class="text-md tracking-tight text-neutral my-5">
                        <span class="font-medium">Total Cost: </span>₱ {{ number_format($r_list->total, 2) }}
                    </p>
                </div>
            @endif
        </div>
        <div class="divider"></div>
        <div class="flex flex-wrap justify-center w-full">
            <div class="w-96 rounded">
                <h2 class="text-2xl mb-5 font-bold">Verify</h2>
                <h2 class="text-xl"><span class=" font-semibold">First Name: </span>{{$r_list->userReservation->first_name}}</h2>
                <h2 class="text-xl"><span class=" font-semibold">Last Name: </span>{{$r_list->userReservation->last_name}}</h2>
                <h2 class="text-xl"><span class=" font-semibold">Birthday: </span>{{ \Carbon\Carbon::createFromFormat('Y-m-d', $r_list->userReservation->birthday)->format('F j, Y')}}</h2>
                <h2 class="text-xl"><span class=" font-semibold">Country: </span>{{$r_list->userReservation->country}}</h2>
                <h2 class="text-xl"><span class=" font-semibold">Nationality: </span>{{$r_list->userReservation->nationality}}</h2>
            </div>
            <div class="w-96 rounded">
                <img src="{{route('private.image', ['folder' => explode('/', $r_list->valid_id)[0], 'filename' => explode('/', $r_list->valid_id)[1]])}}" alt="Valid ID">
            </div>
        </div>
        <div class="divider"></div>
        <article class="text-md tracking-tight text-neutral my-5 px-0 md:px-24 w-auto">
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
        <div class="flex justify-end space-x-1">
            <x-reservation-action :data="$r_list" />
        </div>
    </x-system-content>
</x-system-layout>