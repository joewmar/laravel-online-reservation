@php
  $totalPrice = 0;
@endphp
<x-landing-layout noFooter>
  <x-navbar activeNav="My Reservation" type="plain"/>

  <x-full-content>
    <section x-data="{loader: false}" class="pt-24 p-5">
      <x-loader />
      <div class="flex justify-center pb-10 text-center ">
        <ul class="w-full text-xs md:text-sm steps steps-horizontal">
          <li data-content="✓" class="step step-primary">Dates</li>
          <li data-content="✓" class="step step-primary">Tour Menu</li>
          <li class="step step-primary">Confirmation</li>
        </ul>
      </div>
      <div x-data="{currency: '{{request('cur') ?? 'PHP'}}'}">
        <h1 class="sr-only">Checkout</h1>
        <form @submit="event.preventDefault();" id="reservation-form" action="{{ route('user.reservation.edit.update', $id)}}" method="POST">
          @csrf
          @method('PUT')
          @if($uinfo['at'] == 'Room Only')
              <div class="mx-auto max-w-screen-xl flex justify-center">
          @else
              <div class="mx-auto grid max-w-screen-xl grid-cols-1 md:grid-cols-2 grid-">

          @endif
              <div class="bg-base-100 py-12 md:py-24">
                <div class="mx-auto max-w-lg space-y-8 px-4 lg:px-8">
                  <div class="flex justify-between items-center w-full">
                    <div class="flex items-center gap-4">
                      <span class="h-10 w-10 rounded-full bg-primary"></span>
                      <h2 class="font-medium text-neutral">Change Reservation Information</h2>
                    </div>
                  </div>
                  <div class="space-y-1 md:space-y-3">
                    <p class="text-md font-medium tracking-tight text-neutral">
                      <strong>Check-in: </strong> {{ \Carbon\Carbon::createFromFormat('Y-m-d', $uinfo['cin'])->format('l F j, Y')}}
                    </p>              
                    @if($uinfo['at'] !== 'Day Tour')        
                      <p class="text-md font-medium tracking-tight text-neutral">
                        <strong>Check-out: </strong> {{ \Carbon\Carbon::createFromFormat('Y-m-d', $uinfo['cout'])->format('l F j, Y')}}
                      </p>  
                    @endif     
                    <p class="text-md font-medium tracking-tight text-neutral">
                      <strong>Number of Guest: </strong> {{$uinfo['px'] > 1 ? $uinfo['px'] . ' guests' : $uinfo['px'] . ' guest' }}
                    </p>         
                    @if($uinfo['at'] !== 'Room Only')
                      <p class="text-md font-medium tracking-tight text-neutral">
                        <strong>Number of guest going on a tour: </strong> {{$uinfo['tpx'] > 1 ? $uinfo['tpx'] . ' guests' : $uinfo['tpx'] . ' guest' }}
                      </p>     
                    @endif   
                    <p class="text-md font-medium tracking-tight text-neutral">
                      <strong>Type: </strong> {{$uinfo['at'] ?? ''}}
                    </p>            
                    <p class="text-md font-medium tracking-tight text-neutral">
                      <strong>Payment Method: </strong> {{$uinfo['py'] ?? ''}}
                    </p>            
                  </div>
                  @if($uinfo['at'] == 'Room Only')
                  <div class="grid grid-cols-2 gap-4 mt-5">
                    <a href="{{route('user.reservation.edit.step2', $id)}}" class="btn btn-ghost w-full">
                      Back
                    </a>
                    <label for="reservation_confirm" class="btn btn-primary w-full">
                      Confirm
                    </label>
  
                    <x-modal id="reservation_confirm" title="Confirmation" type="YesNo" formID="reservation-form" loader=true>
                      <p class="">Are you sure your correct your information?</p>
                    </x-modal>
                  </div>
                @endif
                </div>
              </div>
              
              <div class="bg-white py-12 md:py-24">
                <div class="mx-auto max-w-lg px-4 lg:px-8">
                  <div class="flow-root">
                        @if($uinfo['at'] !== 'Room Only')
                        <div class="w-full overflow-x-auto mt-5">
                          <table class="table table-zebra table-xs">
                            <!-- head -->
                            <thead>
                              <tr>
                                <th>Tour</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Amount</th>
                                <th></th>
                              </tr>
                            </thead>
                            <tbody>
                              <!-- rowS -->
                              @if(isset($user_menu))
                                  @foreach ($user_menu as $key => $item)
                                      <tr>
                                        <td>{{$item['title']}}</td>
                                        <td>{{$item['type']}}</td>
                                        <td>{{$uinfo['tpx']}}</td>
                                        <td>
                                            <input type="hidden" name="tour[]" value="{{encrypt($item['id'])}}">
                                            ₱ {{ number_format($item['price'], 2) }}
                                        </td>
                                        <td>
                                          ₱ {{ number_format($item['amount'], 2) }}
                                        </td>
                                        <td>
                                          <label for="remove-tour" class="btn btn-ghost btn-circle btn-sm text-error">
                                            <i class="fa-solid fa-trash"></i>                                              
                                          </label>
                                          <x-modal id="remove-tour" title="Do you want delete this: {{ $item['title']}}" loader=true>
                                            <div class="modal-action">
                                              <a href="{{route('reservation.tour.destroy', encrypt($item['id']) )}}" class="btn btn-primary">Yes</a>
                                              <label for="remove-tour" class="btn btn-ghost">No</label>
                                            </div>
                                          </x-modal>
                                        </td>
                                        @php $totalPrice += (double)$item['amount']  @endphp
                                      </tr>

                                  @endforeach
                              @else
                                <tr colspan="2">
                                  <td>No Cart</td>
                                </tr>
                              @endif
                            </tbody>
                          </table>
                        
                        </div>
                        <div class="flex justify-end mb-5">
                          <p class="text-md font-medium tracking-tight text-neutral">
                            Total Cost: ₱ {{ number_format($totalPrice, 2) }}
                          </p>
                        </div>
                        @endif
                        @if($uinfo['at'] != 'Room Only')
                          <div class="grid grid-cols-2 gap-4 mt-5">
                            <a href="{{route('user.reservation.edit.step2', $id)}}" class="btn btn-ghost w-full">
                              Back
                            </a>
                            <label for="reservation_confirm" class="btn btn-primary w-full">
                              Confirm
                            </label>
          
                            <x-modal id="reservation_confirm" title="Confirmation" type="YesNo" formID="reservation-form" loader=true>
                              <p class="">Are you sure your correct your information?</p>
                            </x-modal>
                          </div>
                        @endif
                  </div>
              </div>
              </div>
          </div>
      </form>
      </div>
    </section>
  </x-full-content>
</x-landing-layout>
