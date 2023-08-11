@php
    $arrAccType = ['Room Only', 'Day Tour', 'Overnight'];
    $arrPayment = ['Walk-in', 'Other Booking', 'Gcash', 'Paypal'];
    $arrStatus = ['Pending', 'Confirmed', 'Check-in', 'Previous', 'Previous', 'Reshedule', 'Cancel', 'Disaprove'];
@endphp
<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Edit {{$r_list->userReservation->name()}}" back=true>
        <form id="edit-form" method="POST" action="{{route('system.reservation.update', encrypt($r_list->id))}}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <section class="w-full flex justify-center">
                <div class="w-96">
                    <h2 class="text-lg my-5">Reservation Information</h2>
                    <x-input type="number" name="age" id="age" placeholder="{{$r_list->userReservation->name()}} Age" value="{{old('age') ?? $r_list->age}}"/>
                    <x-datetime-picker name="check_in" id="check_in" placeholder="Check in" class="flatpickr-reservation" value="{{old('check_in') ?? $r_list->check_in}}"/>
                    <x-datetime-picker name="check_out" id="check_out" placeholder="Check out" class="flatpickr-reservation flatpickr-input2" value="{{old('check_out') ?? $r_list->check_out}}" />
                    <x-select name="accommodation_type" id="accommodation_type" placeholder="Accommodation Type" :value="$arrAccType" :title="$arrAccType" selected="{{old('accommodation_type') ?? $r_list->accommodation_type}}" />
                    <x-input type="number" name="pax" id="pax" placeholder="Number of Guests" value="{{old('pax') ?? $r_list->pax}}"/>
                    <x-input type="number" name="tour_pax" id="tour_pax" placeholder="How many people will be going on the tour" value="{{old('tour_pax') ?? $r_list->tour_pax}}" />
                    <x-select id="payment_method" name="payment_method" placeholder="Payment Method" :value="$arrPayment"  :title="$arrPayment" selected="{{old('payment_method') ?? $r_list->payment_method}}"/>
                    <x-select id="status" name="status" placeholder="Status" :value="array_keys($arrStatus)"  :title="$arrStatus" selected="{{$arrStatus[old('payment_method')] ?? $r_list->status()}}" />
                </div>
            </section>
            <div class="divider"></div>
            <section class="w-full">
                <h2 class="text-lg my-5">Room Information</h2>
                <div class="form-control w-full">
                    <label for="room_rate" class="w-full relative flex justify-start rounded-md border border-base-200 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary ">
                            <select name="room_rate" id="room_rate" class='w-full select select-primary peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0'>
                            <option value="" disabled selected>Please select</option>
                            @foreach ($rates as $key => $rate)
                                @if(old('room_rate') == $rate->id);
                                    <option value="{{$rate->id}}" selected>{{$rate->name}} ({{$rate->occupancy}} pax)</option>
                                @elseif($rate->occupancy == $r_list->pax)
                                    <option value="{{$rate->id}}" selected>{{$rate->name}} ({{$rate->occupancy}} pax)</option>
                                @else
                                    <option value="{{$rate->id}}">{{$rate->name}} ({{$rate->occupancy}} pax)</option>
                                @endif
                            @endforeach
                        </select>        
                        <span id="room_rate" class="pointer-events-none absolute start-2.5 top-0 -translate-y-1/2 bg-white p-0.5 text-xs text-neutral transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-0 peer-focus:text-xs">
                            Room Type
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
                <div x-data="{rooms: []}"class="flex flex-wrap justify-center md:justify-normal flex-grow m-5 gap-5 w-full">
                    @forelse ($rooms as $key => $item)
                        <div x-init="{{array_key_exists($r_list->id, $item->customer ?? []) ? 'rooms.push('.$item->id.')' : ''}}" id="{{$item->availability == 1 ? 'disabledAll' : ''}}">
                            @if($item->availability == 1)
                                <input type="checkbox" x-model="rooms" value="{{$item->id}}" id="RoomNo{{$item->room_no}}" class="peer hidden [&:checked_+_label_span]:h-full [&:checked_+_label_span_h4]:block [&:checked_+_label_span_div]:block" disabled/>
                            @elseif(isset($item->customer[$r_list->id]) && $item->customer[$r_list->id] > 0)
                                <input type="checkbox" x-model="rooms" value="{{$item->id}}" id="RoomNo{{$item->room_no}}" class="peer hidden [&:checked_+_label_span]:h-full [&:checked_+_label_span_h4]:block [&:checked_+_label_span_div]:block" checked />
                            @else
                                <input type="checkbox" x-model="rooms" value="{{$item->id}}" id="RoomNo{{$item->room_no}}" class="peer hidden [&:checked_+_label_span]:h-full [&:checked_+_label_span_h4]:block [&:checked_+_label_span_div]:block"/>
                            @endif
                            <label for="RoomNo{{$item->room_no}}">
                                <div class="relative w-52 overflow-hidden rounded-lg border p-4 sm:p-6 lg:p-8 {{$item->availability == 1 ? 'opacity-70 bg-red-600' : 'border-primary cursor-pointer'}}">
                                    @if($item->availability == 1)
                                        <span class="absolute inset-x-0 bottom-0 h-full  bg-red-500 opacity-80 flex items-center"><h4 class="text-base-100 block font-medium w-full text-center">Reserved</h4></span>
                                    @else
                                        <span class="absolute inset-x-0 bottom-0 h-3 bg-primary flex flex-col items-center justify-center">
                                            <h4 class="text-primary-content hidden font-medium w-full text-center">Room No. {{$item->room_no}} Selected</h4> 
                                            <div x-data="{count: {{array_key_exists($r_list->id, $item->customer ?? []) ? $item->customer[$r_list->id] : 1 }}}" class="join hidden">
                                                <button @click="count > 1 ? count-- : count = 1" type="button" class="btn btn-accent btn-xs join-item rounded-l-full">-</button>
                                                <input x-model="count" type="number" :name="rooms.includes({{$item->id}}) ? 'room_pax[{{$item->id}}]' : '' " class="input input-bordered w-10 input-xs input-accent join-item" min="1" max="{{$r_list->pax}}" readonly/>
                                                <button @click="count < {{$r_list->pax}} ? count++ : count = {{$r_list->pax}}" type="button" class="btn btn-accent btn-xs last:-item rounded-r-full">+</button>
                                            </div>
                                        </span>
                                    @endif
                                    <div class="sm:flex sm:justify-between sm:gap-4">
                                        <div>
                                            <h3 class="text-lg font-bold text-neutral sm:text-xl">Room No. {{$item->room_no}}</h3>
                                            <p class="mt-1 text-xs font-medium text-gray-600">{{$item->room->name}}</p>
                                            <p class="mt-1 text-xs font-medium text-gray-600">{{$item->room->min_occupancy}} up to {{$item->room->max_occupancy}} capacity</p>
                                            @if($item->getAllPax() === (int)$item->room->max_occupancy - 1)
                                                <p class="mt-1 text-xs font-bold text-red-400">There is only {{$item->getAllPax()}} guest</p>
                                            @else
                                                <p class="mt-1 text-sm font-medium ">{{$item->getAllPax() > 0 ? $item->getAllPax() . ' guest availed' : 'No guest'}} </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    @empty
                        <p class="text-2xl font-semibold">No Record Found</p>
                    @endforelse
                </div>
                @if (!empty($tour_menu))
                    <div class="divider"></div>
                    <h2 class="text-lg my-5">Tour Information: Remove List</h2>
                    <div class="overflow-x-auto">
                        <table class="table">
                        <!-- head -->
                        <thead>
                            <tr>
                            <th></th>
                            <th>Tour</th>
                            <th>Price</th>
                            <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody x-data="{tourMenu: []}">
                            @forelse ($tour_menu as $key => $item)
                                <tr>
                                    <th>
                                    <label>
                                        <input x-model="tourMenu" type="checkbox" name="tour_menu[]" class="checkbox checkbox-error" value="{{encrypt($item['id'])}}" />
                                    </label>
                                    </th>
                                    <td>{{$item['title']}}</td>
                                    <td>₱ {{number_format($item['price'], 2)}}</td>
                                    <td>₱ {{number_format($item['amount'], 2)}}</td>
                                </tr>
                            @empty
                                <tr colspan="4">
                                    <td class="text-center font-bold">No Tour Found</td>
                                </tr>
                            @endforelse


                        </tbody>
        
                        </table>
                    </div>
                @endif
                @if(!empty($tour_addons))
                    <div class="divider"></div>
                        <h2 class="text-lg my-5">Addtional Request Information: Tour Service</h2>
                        <div class="overflow-x-auto">
                        <table class="table">
                    <!-- head -->
                            <thead>
                                <tr>
                                    <th>
        
                                    </th>
                                    <th>Tour</th>
                                    <th>Price</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody x-data="{tourAddons: []}">
                                @forelse ($tour_addons as $key => $item)
                                    <tr>
                                        <th>
                                        <label>
                                            <input x-model="tourAddons" name="tour_addons[]" type="checkbox" class="checkbox checkbox-error" value="{{encrypt($item['id'])}}" />
                                        </label>
                                        </th>
                                        <td>{{$item['title']}}</td>
                                        <td>₱ {{number_format($item['price'], 2)}}</td>
                                        <td>₱ {{number_format($item['amount'], 2)}}</td>
                                    </tr>
                                @empty
                                    <tr colspan="4">
                                        <td class="text-center font-bold">No Tour Found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
                @if(!empty($other_addons))
                    <div class="divider"></div>
                        <h2 class="text-lg my-5">Addtional Request Information: Other Service</h2>
                        <div class="overflow-x-auto">
                        <table class="table">
                    <!-- head -->
                            <thead>
                                <tr>
                                    <th>
                                    </th>
                                    <th>Addons</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody x-data="{otherAddons: []}">
                                @forelse ($other_addons as $key => $item)
                                    <tr>
                                        <th>
                                            <label>
                                                <input type="checkbox" name="other_addons[]" class="checkbox checkbox-error" value="{{encrypt($item['id'])}}" />
                                            </label>
                                        </th>
                                        <td>{{$item['title']}}</td>
                                        <td>{{$item['pcs']}}</td>
                                        <td>₱ {{number_format($item['price'], 2)}}</td>
                                        <td>₱ {{number_format($item['amount'], 2)}}</td>
                                    </tr>
                                @empty
                                    <tr colspan="4">
                                        <td class="text-center font-bold">No Tour Found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
                <div class="divider"></div>
                <div class="flex w-full justify-center">
                    <div class="w-96">
                        <h2 class="text-lg my-5">Valid ID Information</h2>
                        <div class="w-96 rounded">
                            <img src="{{route('private.image', ['folder' => explode('/', $r_list->valid_id)[0], 'filename' => explode('/', $r_list->valid_id)[1]])}}" alt="Valid ID">
                        </div>
                        <x-file-input id="valid_id" name="valid_id" placeholder="Send Valid ID" value="{{ route('private.image', ['folder' => explode('/', $r_list->valid_id)[0], 'filename' => explode('/', $r_list->valid_id)[1]]) }}" />
                    </div>
                </div>
                <div class="flex justify-end space-x-3">
                    <label for="save_reservation" class="btn btn-primary">Save</label>
                    <x-modal formID="edit-form" id="save_reservation" title="Do you want change information of {{$r_list->userReservation->name()}}" type="YesNo" loader=true >
                    </x-modal>
                    {{-- <button class="btn btn-error">Remove</button> --}}
                </div>
            </section>
        </form>
    </x-system-content>
</x-system-layout>