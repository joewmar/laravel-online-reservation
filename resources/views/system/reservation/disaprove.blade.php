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
            <article class="text-md tracking-tight text-neutral my-5 px-0 md:px-24 w-auto">
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
        <div class="divider"></div>
        <article x-data="{reason: ''}" class="text-md tracking-tight text-neutral my-5 px-0 md:px-24 w-auto">
            <h2 class="text-2xl mb-5 font-bold">Why Disaprove Request of {{$r_list->userReservation->first_name}} {{$r_list->userReservation->last_name}}</h2>
            <div class="form-control w-full">
                <label for="room_rate" class="w-full relative flex justify-start rounded-md border border-base-200 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary ">
                    <select x-model="reason" name="reason" id="reason" class='w-full select select-primary peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0'>
                        <option value="" disabled selected>Please select</option>
                        <option value="No Room Available">No Room Available</option>
                        <option value="Unable to pay the downpayment">Unable to pay the downpayment</option>
                        <option value="Other" selected>Other</option>
                    </select>        
                    <span id="room_rate" class="pointer-events-none absolute start-2.5 top-0 -translate-y-1/2 bg-white p-0.5 text-xs text-neutral transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-0 peer-focus:text-xs">
                        Reason To Disaprove
                    </span>
                </label>
                <label class="label">
                    <span class="label-text-alt">
                        @error('room_rate')
                            <span class="label-text-alt text-error">{{$message}}</span>
                        @enderror
                    </span>
                </label>
            </div>
            <div x-show="reason == 'Other' " class="my-5">
                <span class="text-xl font-medium ">Other</span>
                <div class="mt-3">
                    <x-textarea name="message" id="message" placeholder="Reason Message" />
                </div>
            </div>    
        </article>
        <div class="flex justify-end space-x-1">
            <label for="reservation" class="btn btn-error btn-sm">Disaprove</label>
            <a href="{{route('system.reservation.show.rooms', encrypt($r_list->id))}}" class="btn btn-secondary btn-sm">Back</a>
            <form action="" method="post">
                @csrf
                <x-passcode-modal title="Disaprove Confirmation" id="reservation" formId="reservation-form" />        
            </form>
        </div>
    </x-system-content>
</x-system-layout>