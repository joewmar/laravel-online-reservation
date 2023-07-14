<x-system-layout :activeSb="$activeSb">
    <x-system-content title="">
        {{-- User Details --}}
        <div class="w-full p-8 sm:flex sm:space-x-6">
            <div class="flex-shrink-0 mb-6 h-15 sm:h-32 w-15 sm:w-32 sm:mb-0">
                <img src="{{asset('images/avatars/no-avatar.png')}}" alt="" class="object-cover object-center w-full h-full rounded">
            </div>
            <div class="flex flex-col space-y-4">
                <div>
                    <h2 class="text-2xl font-semibold">{{$r_list->userReservation->first_name}} {{$r_list->userReservation->last_name}}</h2>
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
        <div class="divider"></div>
        @if($r_list->accommodation_type !== 'Room Only')
            <div class="block md:flex items-center justify-around">
            <article class="text-md tracking-tight text-neutral my-5 p-5 w-auto">
        @else
            <div class="block w-full">
            <article class="text-md tracking-tight text-neutral my-5 px-24 w-auto">
        @endif
                <h2 class="text-2xl mb-5 font-bold">Details</h2>
                <p class="my-1"><strong>Number of Guest: </strong>{{$r_list->pax ?? 'None'}}</p>
                <p class="my-1"><strong>Type: </strong>{{$r_list->accommodation_type ?? 'None'}}</p>
                <p class="my-1"><strong>Room No: </strong>{{$r_list->room_id ?? 'None'}}</p>
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
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($menu as $key => $item)
                                <tr>
                                    <td>{{$item['title']}}</td> 
                                    <td>{{number_format(explode(',', $r_list->amount)[$key], 2)}}</td> 
                                </tr>
                            @endforeach
                        </tbody>
                        </table>
                    </div>
                    <p class="text-md tracking-tight text-neutral my-5">
                        <span class="font-medium">Total Cost: </span>P {{ number_format($r_list->total, 2) }}
                    </p>
                </div>
            @endif
        </div>
        <div class="flex justify-end space-x-1">
            @php
                $title = ''; 
            @endphp
            @if($r_list->status() == "Pending")
                <a href="{{route('system.reservation.show.rooms', encrypt($r_list->id))}}" class="btn btn-secondary btn-sm">Confirm</a>
                <label for="reservation" class="btn btn-success btn-sm" disabled>Check-in</label>
                <label for="reservation" class="btn btn-info btn-sm" disabled>Check-out</label>
            @elseif($r_list->status() == "Confirmed")
                @php $title = 'Check-in' @endphp
                <a href="" class="btn btn-secondary btn-sm">Confirm</a>
                <label for="reservation" class="btn btn-success btn-sm">Check-in</label>
                <label for="reservation" class="btn btn-info btn-sm" disabled>Check-out</label>

            @elseif($r_list->status() == "Check-in")
                @php $title = 'Check-out' @endphp
                <a href="" class="btn btn-secondary btn-sm">Confirm</a>
                <label for="reservation" class="btn btn-success btn-sm" disabled>Check-in</label>
                <label for="reservation" class="btn btn-info btn-sm">Check-out</label>
            @endif
            <form action="" method="post">
                @csrf
                <x-passcode-modal title="{{$title}} Confirmation" id="reservation" formId="reservation-form" />        
            </form>
        </div>
    </x-system-content>
</x-system-layout>
