@php
  $totalPrice = 0;
@endphp
<x-landing-layout>
  <x-full-content>
    <section x-data="{loader: false}" class="my-10 p-5">
      <x-loader />
      <div class="flex justify-center item- pb-10 text-center ">
        <ul class="w-full steps steps-horizontal">
          <li data-content="✓" class="step step-primary">Dates</li>
          <li data-content="✓" class="step step-primary">Tour Menu</li>
          <li data-content="✓" class="step step-primary">Your Details</li>
          <li class="step step-primary">Confirmation</li>
        </ul>
      </div>
      <div x-data="{currency: '{{request('cur') ?? 'PHP'}}'}">
        <h1 class="sr-only">Checkout</h1>
        <form id="convertion-from" action="{{route('reservation.convert')}}" method="post" class="hidden" >
          @csrf
          <input type="hidden" name="cur" :value="currency">
        </form>
        <form id="reservation-form" action="{{ route('reservation.store')}}" method="POST" enctype="multipart/form-data">
          @csrf
        <div class="mx-auto grid max-w-screen-xl grid-cols-1 md:grid-cols-2 grid-">
            <div class="order-last md:order-first bg-base-100 py-12 md:py-24">
              <div class="mx-auto max-w-lg space-y-8 px-4 lg:px-8">
                <div class="flex justify-between items-center w-full">
                  <div class="flex items-center gap-4">
                    <span class="h-10 w-10 rounded-full bg-primary"></span>
                    <h2 class="font-medium text-neutral">Your Cart</h2>
                  </div>
                  <div class="flex items-center gap-4">
                        <div class="join">
                          <select x-model="currency" class="select select-bordered select-primary select-sm join-item text-xs">
                            @foreach ($currencyKey as $item)
                              @if ((old('cur') ?? 'PHP') === $item || (request('cur') ?? 'PHP') === $item)
                                <option value="{{$item}}" selected>{{$item}}</option>
                              @else
                                <option value="{{$item}}">{{$item}}</option>
                              @endif
                            @endforeach
                          </select>
                          <div>
                            <button @click="loader = true; event.preventDefault(); document.getElementById('convertion-from').submit();" class="btn join-item btn-primary btn-sm">Convert</button>
                          </div>
                      </div>
                  </div>
                </div>
                <div class="space-y-3">
                  <p class="text-md font-medium tracking-tight text-neutral">
                    <strong>Number of Guest: </strong> {{$uinfo['px'] > 1 ? $uinfo['px'] . ' guests' : $uinfo['px'] . ' guest' }}
                  </p>            
                  <p class="text-md font-medium tracking-tight text-neutral">
                    <strong>Number of guest going on a tour: </strong> {{$uinfo['tpx'] > 1 ? $uinfo['tpx'] . ' guests' : $uinfo['tpx'] . ' guest' }}
                  </p>            
                  <p class="text-md font-medium tracking-tight text-neutral">
                    <strong>Type: </strong> {{$uinfo['at'] ?? ''}}
                  </p>            
                  <p class="text-md font-medium tracking-tight text-neutral">
                    <strong>Payment Method: </strong> {{$uinfo['py'] ?? ''}}
                  </p>            
                </div>
                <div>
                    <div class="flow-root">
                          @if($uinfo['at'] !== 'Room Only')
                          <div class="overflow-x-auto ">
                            <table class="table table-zebra table-xs">
                              <!-- head -->
                              <thead>
                                <tr>
                                  <th>Tour</th>
                                  <th>Type</th>
                                  <th>Price</th>
                                  <th>Amount</th>
                                  <th></th>
                                </tr>
                              </thead>
                              <tbody>
                                <!-- rowS -->
                                @if(session()->has('rinfo'))
                                    @foreach ($user_menu as $key => $item)
                                        <tr>
                                          <td>{{$item['title']}}</td>
                                          <td>{{$item['type']}}</td>
                                          {{-- <td>{{$item['pax']}}</td> --}}
                                          <td>
                                              <input type="hidden" name="amount[]" value="{{encrypt($item['id'])}}">
                                              {{$currencies[request('cur')] ?? '₱'}} {{ number_format($item['price'], 2) }}
                                          </td>
                                          <td>
                                            {{$currencies[request('cur')] ?? '₱'}} {{ number_format($item['amount'], 2) }}
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
                          <div class="flex justify-end mb-5">
                            <p class="text-md font-medium tracking-tight text-neutral">
                              Total Cost: {{$currencies[request('cur')] ?? '₱'}} {{ number_format($totalPrice, 2) }}
                            </p>
                          </div>
                          @endif
                      <div>
                        <p class="text-md font-medium tracking-tight text-error">
                          Note: The availability of the room depends on the number of guests, so please wait for the approval to process your reservation.
                        </p>
                      </div>
                    </div>
                </div>
              </div>
              <div class="col-span-6 grid md:hidden grid-cols-2 gap-4 ">
                <a href="{{route('reservation.date')}}" class="btn btn-ghost w-full">
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
            
            <div class="order-first md:order-last bg-white py-12 md:py-24">
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
                  <div class="col-span-6">
                    <div class="mb-5 py-4 w-full flex flex-col justify-center items-center">
                      <div class="rounded">
                        <img id="show_img" src="{{asset('images/logo.png')}}" alt="Valid ID"  class="object-center w-80 shadow-lg" />
                      </div> 
                    </div>
                    <x-file-input id="valid_id" name="valid_id" placeholder="Send Valid ID" />

                  </div>
    
                  <div class="col-span-6 hidden md:grid grid-cols-2 gap-4 ">
                    <a href="{{route('reservation.date')}}" class="btn btn-ghost w-full">
                      Change
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
  </x-full-content>

</x-landing-layout>
