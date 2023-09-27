@php
  $totalPrice = 0;
  $arrStatus = ['Pending', 'Confirmed', 'Check-in', 'Previous', 'Previous', 'Reshedule', 'Cancel', 'Disaprove'];
@endphp
<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Add Book" back=true>
    <section x-data="{loader: false}" class="my-10 p-5">
      <x-loader />
      <div>
        <h1 class="sr-only">Reservation Informatio</h1>
        <form id="reservation-form" action="{{ route('system.reservation.store.step.four')}}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="mx-auto grid max-w-screen-xl grid-cols-1 md:grid-cols-2 grid-">
              <div class="order-last md:order-first bg-base-100 py-12 md:py-24">
                <div class="mx-auto max-w-lg space-y-8 px-4 lg:px-8">
                  <div class="flex justify-between items-center w-full">
                    <div class="flex items-center gap-4">
                      <span class="h-10 w-10 rounded-full bg-primary"></span>
                    </div>
                  </div>
                  <div class="space-y-1 md:space-y-3">
                    <p class="text-md font-medium tracking-tight text-neutral">
                      <strong>Check-in: </strong> {{ \Carbon\Carbon::createFromFormat('Y-m-d', $other_info['cin'])->format('l F j, Y')}}
                    </p>                      
                    <p class="text-md font-medium tracking-tight text-neutral">
                      <strong>Check-out: </strong> {{ \Carbon\Carbon::createFromFormat('Y-m-d', $other_info['cout'])->format('l F j, Y')}}
                    </p>     
                    <p class="text-md font-medium tracking-tight text-neutral">
                      <strong>Number of Guest: </strong> {{$other_info['px'] > 1 ? $other_info['px'] . ' guests' : $other_info['px'] . ' guest' }}
                    </p>                  
                    @if(isset($other_info['tpx']))
                      <p class="text-md font-medium tracking-tight text-neutral">
                        <strong>Number of guest going on a tour: </strong> {{$other_info['tpx'] > 1 ? $other_info['tpx'] . ' guests' : $other_info['tpx'] . ' guest' }}
                      </p>   
                    @endif
                    <p class="text-md font-medium tracking-tight text-neutral">
                      <strong>Type: </strong> {{$other_info['at'] ?? ''}}
                    </p>            
                    <p class="text-md font-medium tracking-tight text-neutral">
                      <strong>Payment Method: </strong> {{$other_info['py'] ?? ''}}
                    </p>            
                    <p class="text-md font-medium tracking-tight text-neutral">
                      <strong>Status: </strong> {{$arrStatus[$other_info['st']] ?? ''}}
                    </p>            
                    <p class="text-md font-medium tracking-tight text-neutral">
                      <strong>Room Assign: </strong> {{$rooms ?? 'None'}}
                    </p>            
                    <p class="text-md font-medium tracking-tight text-neutral">
                      <strong>Room Type: </strong> {{$rate->name ?? 'None'}}
                    </p>            
                  </div>
                  <div>
                      <div class="flow-root">
                            @if(!empty($tour_menus))
                            <h1 class="text-md font-semibold mt-5">Tour Services</h1>
                              <div class="overflow-x-auto ">
                                <table class="table table-zebra table-xs">
                                  <!-- head -->
                                  <thead>
                                    <tr>
                                      <th>Tour</th>
                                      <th>Guest</th>
                                      <th>Price</th>
                                      <th>Amount</th>
                                      {{-- <th></th> --}}
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <!-- rowS -->
                                    @foreach ($tour_menus as $key => $item)
                                        <tr>
                                          <td>{{$item['title']}}</td>
                                          <td>{{$other_info['px']}}</td>
                                          <td>{{$item['price']}}</td>
                                          <td>₱ {{number_format($item['amount'], 2)}} </td>
                                          @php $totalPrice += (double)$item['amount']  @endphp
                                        </tr>

                                    @endforeach
                                  </tbody>
                                </table>
                              </div>
                            @endif
                            @if(!empty($addons))
                              <h1 class="text-md font-semibold mt-5">Addtional Request</h1>
                              <div class="overflow-x-auto ">
                                <table class="table table-zebra table-xs">
                                  <!-- head -->
                                  <thead>
                                    <tr>
                                      <th>Addons</th>
                                      <th>Quantity</th>
                                      <th>Price</th>
                                      <th>Amount</th>
                                      {{-- <th></th> --}}
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <!-- rowS -->
                                    @foreach ($addons as $key => $item)
                                        <tr>
                                          <td>{{$item['title']}}</td>
                                          <td>{{$item['pcs']}}</td>
                                          <td>{{$item['price']}}</td>
                                          <td>₱ {{number_format($item['amount'], 2)}} </td>
                                          @php $totalPrice += (double)$item['amount']  @endphp
                                        </tr>
                                    @endforeach
                                  </tbody>
                                </table>
                              </div>
                            @endif
                            <div class="flex w-full justify-end my-10">
                              <div class="flex flex-col md:items-end ">
                                <p class="text-md tracking-tight text-neutral">
                                  Room Rate: ₱ {{ number_format($rate->price, 2) }}
                                </p>
                                <p class="text-md tracking-tight text-neutral">
                                  No. of days: {{$user_days > 0 ? $user_days . ' days' : $user_days . ' day'  }}
                                </p>
                                <p class="text-md tracking-tight text-neutral">
                                  Total Rate: ₱ {{number_format($rate->price * $user_days, 2)}}
                                  @php $totalPrice += ($rate->price * $user_days) @endphp
                                </p>
                                <p class="text-md font-medium tracking-tight text-neutral mt-5">
                                  Total Cost: ₱ {{ number_format($totalPrice, 2) }}
                                </p>
                              </div>
                            </div>
                      </div>
                  </div>
                </div>
                <div class="col-span-6 grid md:hidden grid-cols-2 gap-4 ">
                  <a href="{{route('system.reservation.create')}}" class="btn btn-ghost w-full">
                    Change
                  </a>
                  <label for="reservation_confirm" class="btn btn-primary w-full">
                    Confirm
                  </label>

                  <x-modal id="reservation_confirm" title="Confirmation" type="YesNo" formID="reservation-form" loader=true>
                    <p class="">Are you sure your correct your information?</p>
                  </x-modal>
                </div>
              </div>
              
              <div class="order-first md:order-last py-12 md:py-24">
                <div class="mx-auto max-w-lg px-4 lg:px-8">
                  <div class="grid grid-cols-6 gap-8">
                    <div class="col-span-6">
                      <x-input name="first_name" id="first_name" placeholder="First Name" />
                    </div>
      
                    <div class="col-span-6">
                      <x-input name="last_name" id="last_name" placeholder="Last Name" />
                    </div>
      
                    <div class="col-span-6">
                      <x-input type="number" name="age" id="age" min="10" placeholder="Age" />
                    </div>
      
                    <div class="col-span-6">
                      <x-datalist-input id="country" name="country" placeholder="Country" :lists="$countries" value="{{old('country') ?? ''}}" />
                    </div>
                    <div class="col-span-6">
                      <x-datalist-input id="nationality" name="nationality" placeholder="Nationality" :lists="$nationality" value="{{old('nationality') ?? ''}}" />
                    </div>
                    <div class="col-span-6">
                      <x-input type="email" name="email" id="email" placeholder="Contact Email" />
                    </div>
                    <div class="col-span-6">
                    <x-phone-input />
                    </div>
                    <div class="col-span-6">
                      <div class="w-full">
                        <x-input type="number" name="payment_amount" id="payment_amount" min="1000" placeholder="Payment Amount" />
                      </div>                    </div>
                    <div class="col-span-6">
                      <x-drag-drop title="Valid ID" name="valid_id" id="valid_id1" />
                    </div>
      
                    <div class="col-span-6 hidden md:grid grid-cols-2 gap-4 ">
                      <a href="{{route('system.reservation.create.step.three')}}" class="btn btn-ghost w-full">
                        Back
                      </a>
                      <label for="reservation_confirm2" class="btn btn-primary w-full">
                        Confirm
                      </label>
      
                      <x-modal id="reservation_confirm2" title="Confirmation" type="YesNo" formID="reservation-form" loader=true>
                        <p class="">Are you sure your correct your information?</p>
                      </x-modal>
                    </div>
                  </div>
                </div>
              </div>
          </div>
      </form>
      </div>
    </section>
  </x-system-content>

</x-system-layout>
