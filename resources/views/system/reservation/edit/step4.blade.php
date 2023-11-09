@php
  $totalPrice = 0;
  $arrStatus = [0 => "Pending", 1 => 'Confirmed', 2 => 'Check-in', 3 => "Check-out", 5 => 'Cancel'];
@endphp
<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Edit Reservation of {{$name}} (Overall)" back="{{route('system.reservation.show', $id)}}">
    <section x-data="{loader: false}" class="my-10 p-5">
      <x-loader />
      <div>
        <h1 class="sr-only">Edit Information</h1>
        <form id="reservation-form" action="{{ route('system.reservation.edit.step4.update', $id)}}" method="POST">
          @csrf
          @method('PUT')
          <div class="mx-auto grid max-w-screen-xl grid-cols-1 md:grid-cols-2 grid-">
              <div class="bg-base-100 py-12 md:py-24">
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
                    @if (!empty($rooms))
                      <p class="text-md font-medium tracking-tight text-neutral">
                        <strong>Room Assign: </strong> {{$rooms ?? 'None'}}
                      </p>   
                    @endif   
                    @if (!empty($rates))
                      <p class="text-md font-medium tracking-tight text-neutral">
                        <strong>Room Type: </strong> {{$rates['name'] ?? 'None'}}
                      </p>   
                    @endif         
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
                                      <th>Quantity</th>
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
                      </div>
                  </div>
                </div>
              </div> 
              <div class="py-12 md:py-24">
                <div class="mx-auto max-w-md px-4 lg:px-8">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-8 place-content-stretch">
                    
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-8"> 
                    <div class="col-span-2">
                      <h2 class="font-bold text-lg mt-10">Transaction</h2>
                      <table class="table mt-5">
                        <tbody>
                          @if(isset($rates))
                            <tr>
                              <th>Rate</th>
                              <td>{{$rates['price'] > 0 ? '₱ ' . number_format($rates['price'], 2) : 'None' }}</td>
                            </tr>
                            <tr>
                              <th>Days</th>
                              <td>{{$user_days}}</td>
                            </tr>
                            @if ($other_info['scount'])
                              <tr>
                                <th>Rate Amount</th>
                                <td>{{$rates['orig_amount'] > 0 ? '₱ ' . number_format($rates['orig_amount'], 2) : 'None' }}</td>
                              </tr>
                              <tr>
                                <th>Senior / PWD Guest</th>
                                <td>{{$other_info['scount']}}</td>
                              </tr>
                              <tr>
                                <th>Discount Rate</th>
                                <td>20%</td>
                              </tr>
                              <tr>
                                <th>Discount Amount</th>
                                <td>{{$rates['amount']}}</td>
                              </tr>
                            @else
                              <tr>
                                <th>Rate Amount Per Person</th>
                                <td>{{$rates['amount'] > 0 ? '₱ ' . number_format($rates['amount'], 2) : 'None' }}</td>
                              </tr>
                              <tr>
                                <th>Total</th>
                                <td>{{isset($other_info['px']) ? '₱ ' . number_format($rates['amount'] * $other_info['px'], 2) : 'None' }}</td>
                              </tr>
                            @endif
                          @endif
                        </tbody>
                      </table>
                    </div>
                    <div class="col-span-2 hidden md:grid grid-cols-2 gap-4 mt-5">
                      @if ($other_info['at'] == 'Room Only')
                        <a href="{{route('system.reservation.edit.step2', $id)}}" class="btn btn-ghost w-full">
                          Back
                        </a>
                      @else
                        <a href="{{route('system.reservation.edit.step3', $id)}}" class="btn btn-ghost w-full">
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
