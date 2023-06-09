@php
  $totalPrice = 0;
@endphp
<x-landing-layout>
  <x-full-content>
    <section class="my-10 p-5">
      <div class="flex justify-center item- pb-10 text-center ">
        <ul class="w-full steps steps-horizontal">
          <li data-content="✓" class="step step-primary">Dates</li>
          <li data-content="✓" class="step step-primary">Tour Menu</li>
          <li data-content="✓" class="step step-primary">Your Details</li>
          <li class="step step-primary">Confirmation</li>
        </ul>
      </div>
      <div>
        <h1 class="sr-only">Checkout</h1>
  
        <div class="mx-auto grid max-w-screen-xl grid-cols-1 md:grid-cols-2">
          <div class="bg-base-100 py-12 md:py-24">
            <div class="mx-auto max-w-lg space-y-8 px-4 lg:px-8">
              <div class="flex items-center gap-4">
                <span class="h-10 w-10 rounded-full bg-primary"></span>
                <h2 class="font-medium text-gray-900">Your Cart</h2>
              </div>
              <div class="space-y-3">
                <p class="text-md font-medium tracking-tight text-gray-900">
                  <strong>Number of Guest: </strong> {{$uinfo['px'] > 1 ? $uinfo['px'] . ' guests' : $uinfo['px'] . ' guest' }}
                </p>            
                <p class="text-md font-medium tracking-tight text-gray-900">
                  <strong>Type: </strong> {{$uinfo['at'] ?? ''}}
                </p>            
                <p class="text-md font-medium tracking-tight text-gray-900">
                  <strong>Payment Method: </strong> {{$uinfo['py'] ?? ''}}
                </p>            
              </div>
              <div>
                <form id="reservation-form" action="{{ route('reservation.store')}}" method="POST">
                  @csrf
                  <div class="flow-root">
                    @if($uinfo['at'] !== 'Room Only')
                      <div class="overflow-x-auto ">
                        <table class="table table-zebra">
                          <!-- head -->
                          <thead>
                            <tr>
                              <th>Tour</th>
                              <th>Price</th>
                            </tr>
                          </thead>
                          <tbody>
                            <!-- rowS -->
                            @if(session()->has('rinfo'))
                                @foreach ($user_menu as $key => $item)
                                    <tr>
                                      <td>{{$item['title']}}</td>
                                      <td>
                                          <input type="hidden" name="amount[]" value="{{$item['price']}}">{{ number_format($item['price'], 2) }}</td>
                                      @php $totalPrice += (double)$item['price']  @endphp
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
                      <div class="float-right mb-5">
                        <p class="text-md font-medium tracking-tight text-gray-900">
                          Total Cost: <span>P </span>{{ number_format($totalPrice, 2) }}
                        </p>
                      </div>
                      @endif
                    <div class="float-left">
                      <p class="text-md font-medium tracking-tight text-error">
                        Note: The availability of the room depends on the number of guests, so please wait for the approval to process your reservation.
                      </p>
                    </div>
                  </div>
                </form>
  
              </div>
  
  
  
            </div>
          </div>
          
          <div class="bg-white py-12 md:py-24">
            <div class="divider flex md:hidden"></div>
            <div class="mx-auto max-w-lg px-4 lg:px-8">
              <div class="grid grid-cols-6 gap-8">
                <div class="col-span-2">
                  <label for="FirstName" class="block text-xs font-medium text-gray-700">
                    First Name
                  </label>
                  <div class="text-neutral text-xl font-medium">{{$uinfo['first_name']}}</div>
                </div>
  
                <div class="col-span-2">
                  <label for="LastName" class="block text-xs font-medium text-gray-700">
                    Last Name
                  </label>
  
                  <div class="text-neutral text-xl font-medium">{{$uinfo['last_name']}}</div>
  
                </div>
  
                <div class="col-span-2">
                  <label for="Email" class="block text-xs font-medium text-gray-700">
                    Age
                  </label>
  
                  <div class="text-neutral text-xl font-medium">{{$uinfo['age']}}</div>
  
                </div>
  
                <div class="col-span-3">
                  <label for="Phone" class="block text-xs font-medium text-gray-700">
                    Country
                  </label>
                  <div class="text-neutral text-xl font-medium">{{$uinfo['country']}}</div>
                </div>
                <div class="col-span-3">
                  <label for="Phone" class="block text-xs font-medium text-gray-700">
                    Nationality
                  </label>
                  <div class="text-neutral text-xl font-medium">{{$uinfo['nationality']}}</div>
                </div>
                <div class="col-span-6">
                  <label for="Phone" class="block text-xs font-medium text-gray-700">
                    Email
                  </label>  
                  <div class="text-neutral text-xl font-medium">{{$uinfo['email']}}</div>
                </div>
                <div class="col-span-6">
                  <label for="Phone" class="block text-xs font-medium text-gray-700">
                    Phone Number
                  </label>
  
                  <div class="text-neutral text-xl font-medium">{{$uinfo['contact']}}</div>
                </div>
  
                <div class="col-span-6 grid grid-cols-2 gap-4">
                  <a href="{{route('reservation.date')}}" class="btn btn-ghost w-full">
                    Change
                  </a>
                  <label for="reservation_confirm" class="btn btn-primary w-full">
                    Confirm
                  </label>
  
                  <x-modal id="reservation_confirm" title="Confirmation" type="YesNo" formID="reservation-form">
                    <p class="">Are you sure your correct your information?</p>
                  </x-modal>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </x-full-content>

</x-landing-layout>
