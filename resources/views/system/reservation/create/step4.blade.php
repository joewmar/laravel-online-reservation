@php
  $totalPrice = 0;
@endphp
<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Add Book" back=true>
    <section x-data="{loader: false}" class="my-10 p-5">
      <x-loader />
      <div>
        <h1 class="sr-only">Checkout</h1>
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
                  </div>
                  <div class="space-y-1 md:space-y-3">
                    <p class="text-md font-medium tracking-tight text-neutral">
                      <strong>Number of Guest: </strong> {{-- {{$uinfo['px'] > 1 ? $uinfo['px'] . ' guests' : $uinfo['px'] . ' guest' }} --}}
                    </p>            
                    <p class="text-md font-medium tracking-tight text-neutral">
                      <strong>Number of guest going on a tour: {{-- </strong> {{$uinfo['tpx'] > 1 ? $uinfo['tpx'] . ' guests' : $uinfo['tpx'] . ' guest' }} --}}
                    </p>            
                    <p class="text-md font-medium tracking-tight text-neutral">
                      <strong>Type: </strong> {{-- {{$uinfo['at'] ?? ''}} --}}
                    </p>            
                    <p class="text-md font-medium tracking-tight text-neutral">
                      <strong>Payment Method: </strong> {{-- {{$uinfo['py'] ?? ''}}--}}
                    </p>            
                  </div>
                  <div>
                      <div class="flow-root">
                            @if(false)
                              <div class="overflow-x-auto mt-5">
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
                                                  <input type="hidden" name="tour[]" value="{{encrypt($item['id'])}}">
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
                      <x-select id="country" name="country" placeholder="Country" :value="$countries" :title="$countries" selected="{{old('country') ?? ''}}" />
                    </div>
                    <div class="col-span-6">
                      <x-select id="nationality" name="nationality" placeholder="Nationality" :value="$nationality" :title="$nationality" selected="{{old('nationality') ?? ''}}" />
                    </div>
                    <div class="col-span-6">
                      <x-input type="email" name="email" id="email" placeholder="Contact Email" />
                    </div>
                    <div class="col-span-6">
                      <x-input type="number" id="contact" name="contact" placeholder="Phone Number"/>
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
  </x-system-content>

</x-system-layout>
