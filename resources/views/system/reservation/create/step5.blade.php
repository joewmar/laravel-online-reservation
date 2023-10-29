@php
  $totalPrice = 0;
  $arrStatus = [1 => 'Confirmed', 2 => 'Check-in', 3 =>'Check-out'];
  $cinpy = floatval($other_info['cinpy']);

@endphp
<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Add Book (Overall)">
    <section x-data="{loader: false}" class="my-10 p-5">
      <x-loader />
      <div>
        <h1 class="sr-only">Reservation Informatio</h1>
        <form id="reservation-form" action="{{ route('system.reservation.store.step.five')}}" method="POST">
          @csrf
          <div class="mx-auto grid max-w-screen-xl grid-cols-1 md:grid-cols-2 grid-">
              <div class="order-last md:order-first bg-base-100 py-12 md:py-24">
                <div class="mx-auto max-w-lg space-y-8 px-4 lg:px-8">
                  <div class="flex justify-between items-center w-full">
                    <div class="flex items-center gap-4">
                      <span class="hidden md:block h-10 w-10 rounded-full bg-primary"></span>
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
                              <div class="overflow-x-auto my-5">
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
                                    @php $totalTour = 0;  @endphp
                                    @foreach ($tour_menus as $key => $item)
                                        <tr>
                                          <td>{{$item['title']}}</td>
                                          <td>{{$other_info['px']}}</td>
                                          <td>{{$item['price']}}</td>
                                          <td>₱ {{number_format($item['amount'], 2)}} </td>
                                          @php $totalPrice += (double)$item['amount']  @endphp
                                          @php $totalTour += (double)$item['amount']  @endphp
                                        </tr>
                                    @endforeach
                                        <tr>
                                          <td></td>
                                          <td></td>
                                          <td>Total</td>
                                          <td>₱ {{number_format($totalTour, 2)}}</td>
                                        </tr>
                                  </tbody>
                                </table>
                              </div>
                            @endif
                            @if(!empty($addons))
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
                                    @php $totalAddons = 0;  @endphp
                                    @foreach ($addons as $key => $item)
                                        <tr>
                                          <td>{{$item['title']}}</td>
                                          <td>{{$item['pcs']}}</td>
                                          <td>{{$item['price']}}</td>
                                          <td>₱ {{number_format($item['amount'], 2)}} </td>
                                          @php $totalPrice += (double)$item['amount']  @endphp
                                          @php $totalAddons += (double)$item['amount']  @endphp
                                        </tr>
                                    @endforeach
                                    <tr>
                                      <td></td>
                                      <td></td>
                                      <td>Total</td>
                                      <td>₱ {{number_format($totalAddons, 2)}}</td>
                                    </tr>
                                  </tbody>
                                </table>
                              </div>
                            @endif
                            <h2 class="font-bold text-lg mt-10">Transaction</h2>
                            <table class="table mt-5">
                              <tbody>
                                @if (isset($other_info['secount']))
                                    <tr>
                                      <th>Room Rate</th>
                                      <td>₱ {{ number_format($rates['price'], 2) }}</td>
                                    </tr>
                                    <tr>
                                      <th>No. of days</th>
                                      <td>{{$user_days > 0 ? $user_days . ' days' : $user_days . ' day'  }}</td>
                                    </tr>
                                    <tr>
                                      <th>Total Rate</th>
                                      <td>₱ {{number_format($rates['orig_amount'], 2)}}</td>
                                    </tr>
                                    <tr>
                                      <th>Senior Guest</th>
                                      <td>{{$other_info['secount']}}</td>
                                    </tr>
                                    <tr>
                                      <th>Discount</th>
                                      <td>20%</td>
                                    </tr>
                                    <tr>
                                      <th>Total Rate with Discount</th>
                                      <td>₱ {{number_format($rates['amount'], 2)}}</td>
                                      @php $totalPrice += ($rates['amount']) @endphp
                                    </tr>
                                @else
                                    <tr>
                                      <th>Room Rate</th>
                                      <td>₱ {{ number_format($rates['price'], 2) }}</td>
                                    </tr>
                                    <tr>
                                      <th>No. of days</th>
                                      <td>{{$user_days > 0 ? $user_days . ' days' : $user_days . ' day'  }}</td>
                                    </tr>
                                    <tr>
                                      <th>Total Rate</th>
                                      <td>₱ {{number_format($rates['amount'], 2)}}</td>
                                      @php $totalPrice += ($rates['amount']) @endphp
                                    </tr>
                                @endif
                                <tr>
                                  <th>Total Cost</th>
                                  <td>₱ {{ number_format($totalPrice, 2) }}</td>
                                </tr>
                                @if($other_info['st'] == 1)
                                <tr>
                                  <th>Downpayment</th>
                                  <td>{{$other_info['dwnpy'] && intval($other_info['dwnpy']) != $other_info['dwnpy'] ? '₱ ' . number_format($other_info['dwnpy'], 2) : 'None' }}</td>
                                </tr>
                                @elseif($other_info['st'] == 2)
                                <tr>
                                  <th>Check-in Paid</th>
                                  <td>{{!($cinpy && intval($cinpy) != $cinpy) && $other_info['cinpy'] == 'full' ? '₱ '. number_format($totalPrice, 2) : '₱ '. number_format($other_info['cinpy'], 2) ?? 'None' }}</td>
                                </tr>
                                @endif
                                <tr>
                                  @php
                                    $balance = 0;
                                    if($other_info['st'] == 1 && $totalPrice > $other_info['dwnpy']){
                                      $balance = abs($totalPrice - $other_info['dwnpy']);
                                    }
                                    if($cinpy && intval($cinpy) != $cinpy && $other_info['st'] == 2 && $totalPrice > $other_info['cinpy']){
                                      $balance = abs($totalPrice - $other_info['cinpy']);
                                    }
                                  @endphp
                                  <th>Balance</th>
                                  <td>{{$balance > 0 ? '₱ ' . number_format($balance, 2) : 'None' }}</td>
                                </tr>
                                <tr>
                                  @php
                                    $refund = 0;
                                    if($other_info['st'] == 1 && $totalPrice < $other_info['dwnpy']){
                                      $refund = abs($totalPrice - $other_info['dwnpy']);
                                    }
                                    if($cinpy && intval($cinpy) != $cinpy && $other_info['st'] == 2 && $totalPrice < $other_info['cinpy']){
                                      $refund = abs($totalPrice - $other_info['cinpy']);
                                    }
                                  @endphp
                                  <th>Refund</th>
                                  <td>{{$refund > 0 ? '₱'.  number_format($refund, 2) : 'None' }}</td>
                                </tr>
                              </tbody>
                            </table>
                      </div>
                  </div>
                </div>
                <div class="col-span-6 grid md:hidden grid-cols-2 gap-4 mt-5">
                  @if(isset($other_info['uid']))
                    <a href="{{route('system.reservation.create.step.four', Arr::query(['uof' => encrypt($other_info['uid'])]))}}" class="btn btn-ghost w-full">
                      Back
                    </a>
                  @else
                    <a href="{{route('system.reservation.create.step.four')}}" class="btn btn-ghost w-full">
                      Back
                    </a>
                  @endif
                  <label for="reservation_confirm" class="btn btn-primary w-full">
                    Confirm
                  </label>

                  <x-modal id="reservation_confirm" title="Confirmation" type="YesNo" formID="reservation-form" loader=true>
                    <p class="">Are you sure your correct your information?</p>
                  </x-modal>
                </div>
              </div> 
              <div class="order-first md:order-last py-12 md:py-24">
                <div class="mx-auto max-w-md px-4 lg:px-8">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-8 place-content-stretch">
                    <div>
                      <h3 class="font-bold">First Name</h3>
                      <p class="">{{$other_info['fn']}}</p>
                    </div>
                    <div>
                      <h3 class="font-bold">Last Name</h3>
                      <p class="">{{$other_info['ln']}}</p>
                    </div>
                    <div>
                      <h3 class="font-bold">Age</h3>
                      <p class="">{{$other_info['age']}}</p>
                    </div>
                    <div>
                      <h3 class="font-bold">Country</h3>
                      <p class="">{{$other_info['ctry']}}</p>
                    </div>
                    <div>
                      <h3 class="font-bold">Nationality</h3>
                      <p class="">{{$other_info['ntnlt']}}</p>
                    </div>
                    <div>
                      <h3 class="font-bold">Email</h3>
                      <p class="">{{$other_info['eml']}}</p>
                    </div>
                    <div>
                      <h3 class="font-bold">Contact</h3>
                      <p class="">{{$other_info['ctct']}}</p>
                    </div>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-8"> 
                    @if(isset($other_info['vid']))
                      <div class="col-span-2 mt-10">
                        <h3 class="font-bold mb-2">Valid ID</h3>
                        <img src="{{route('private.image', ['folder' => explode('/', $other_info['vid'])[0], 'filename' => explode('/', $other_info['vid'])[1]])}}" alt="" class="object-cover object-center w-full h-full rounded">
                      </div>
                    @endif
      
                    <div class="col-span-2 hidden md:grid grid-cols-2 gap-4 mt-5">
                      @if(isset($other_info['uid']))
                        <a href="{{route('system.reservation.create.step.four', Arr::query(['uof' => encrypt($other_info['uid'])]))}}" class="btn btn-ghost w-full">
                          Back
                        </a>
                      @else
                        <a href="{{route('system.reservation.create.step.four')}}" class="btn btn-ghost w-full">
                          Back
                        </a>
                      @endif
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
