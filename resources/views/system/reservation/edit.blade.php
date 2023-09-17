@php
    $arrAccType = ['Room Only', 'Day Tour', 'Overnight'];
    $arrPayment = ['Walk-in', 'Other Booking', 'Gcash', 'PayPal'];
    $arrStatus = ['Pending', 'Confirmed', 'Check-in', 'Previous', 'Previous', 'Reshedule', 'Cancel'];
@endphp
<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Edit {{$r_list->userReservation->name()}}" back=true>
        <form  id="edit-form" method="POST" action="{{route('system.reservation.update', encrypt($r_list->id))}}" enctype="multipart/form-data">
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
                    @if($r_list->status >= 3)
                        <x-select id="status" name="status" placeholder="Status" :value="array_keys($arrStatus)"  :title="$arrStatus" selected="{{$arrStatus[old('status')] ?? $arrStatus[$r_list->status]}}" disabled=true />
                    @else
                        <x-select id="status" name="status" placeholder="Status" :value="array_keys($arrStatus)"  :title="$arrStatus" selected="{{$arrStatus[old('status')] ?? $arrStatus[$r_list->status]}}"  />
                    @endif
                </div>
            </section>
            <div class="divider"></div>
            <section class="w-full">
                <div class="flex justify-between items-center">
                    <div>
                        <div class="text-xl my-5">Room Information {{$r_list->roomid ? '' : '(No Room Yet)'}}</div> 
                        <div class="text-sm mb-5 font-bold">{{$your_rate ? 'Rate: ' . $your_rate['name'] : 'No Rate Yet'}}</div> 
                    </div>
                    <button class="btn btn-primary btn-sm">Change Room</button>
                </div>
                <div class="flex {{$r_list->roomid ? '' : 'opacity-50'}} grid grid-cols-1 md:grid-cols-4 gap-5 w-full" id="{{$r_list->roomid ? '' : 'disabledAll'}}">
                    @forelse ($rooms as $key => $item)
                        <div>
                            <label for="RoomNo{{$item->room_no}}">
                                <div class="relative w-52 overflow-hidden rounded-lg border p-4 sm:p-6 lg:p-8 border-primary">
                                    <span class="{{in_array($item->id, $r_list->roomid) ? 'h-full' : 'h-3'}} absolute inset-x-0 bottom-0 bg-primary flex flex-col items-center justify-center">
                                        <h4 class="{{in_array($item->id, $r_list->roomid) ? 'block' : 'hidden'}} text-primary-content w-full text-center font-bold ">Room No. {{$item->room_no}}</h4> 
                                        @php $roomKey = 0; @endphp
                                        <div class="{{in_array($item->id, $r_list->roomid) ? 'block' : 'hidden'}}">
                                            <h4 class="{{in_array($item->id, $r_list->roomid) ? 'block' : 'hidden'}} text-primary-content font-medium w-full text-center"><span>{{array_key_exists($r_list->id, $item->customer ?? []) ? (int)$item->customer[$r_list->id] : 1 }}</span> Guest</h4> 
                                        </div>
                                    </span>
                                    <div class="sm:flex sm:justify-between sm:gap-4">
                                        <div>
                                            <h3 class="text-lg font-bold text-neutral sm:text-xl">Room No. {{$item->room_no}}</h3>
                                            <p class="mt-1 text-xs font-medium text-gray-600">{{$item->room->name}}</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    @empty
                        <p class="text-2xl font-semibold">No Room Found</p>
                    @endforelse
                </div>

                @if (!empty($tour_menu))
                    <div class="divider"></div>
                    <div class="flex justify-between items-center my-5">
                        <h2 class="text-lg">Tour Information</h2>
                        <button class="btn btn-primary btn-sm">Add Tour</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table x-data="{tourMenu: []}" class="table">
                        <!-- head -->
                        <thead>
                                <th class="flex justify-start items-center">
                                    <button class="btn btn-error btn-xs" type="button" x-show="!(tourMenu.length === 0)" x-transition>Remove</button>
                                </th>
                                <th>Tour</th>
                                <th>Price</th>
                                <th>Amount</th>
                        </thead>
                        <tbody >
                            @forelse ($tour_menu as $key => $item)
                                <tr>
                                    <th>
                                    <label >
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
                                    <th class="flex justify-start items-center">
                                        <button class="btn btn-error btn-xs" type="button" x-show="!(tourMenu.length === 0)" x-transition>Remove</button>
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
                                    <th class="flex justify-start items-center">
                                        <button class="btn btn-error btn-xs" type="button" x-show="!(tourMenu.length === 0)" x-transition>Remove</button>
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
                            <img src="{{route('private.image', ['folder' => explode('/', $r_list->userReservation->valid_id)[0], 'filename' => explode('/', $r_list->userReservation->valid_id)[1]])}}" alt="Valid ID">
                        </div>
                    </div>
                </div>

            </section>
        </form>
    </x-system-content>
</x-system-layout>