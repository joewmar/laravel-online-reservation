@php
    $arrAccType = ['Room Only', 'Day Tour', 'Overnight'];
    $arrAccTypeTitle = ['Room Only', 'Day Tour (Only 1 Day)', 'Overnight (Only 2 days and above)'];
    $arrPayment = ['Gcash', 'PayPal', 'Bank Transfer'];
@endphp

<x-landing-layout noFooter>
  <x-navbar activeNav="My Reservation" type="plain"/>

  <section class="w-full pt-24"
        x-data="{
          @if(request()->has(['py', 'tpx']) && $TourInfo['at'] != 'Room Type')
            filterOpen: false, 
          @else
            filterOpen: true, 
          @endif
          alert: false,
          alertType: '',
          loader: false,
          @if(!empty($tourListCart))
            carts: [
              @foreach($tourListCart ?? [] as $key => $item)
                { id: '{{  $item['id'] ?? '' }}', 
                  title: '{{  $item['title'] ?? '' }}',
                  type: '{{  $item['type'] ?? '' }} ({{ $item['pax'] ?? ''}} guest)',
                  price: '₱ {{isset($item['price']) ? number_format( $item['price'], 2) : '' }}',
                  nprice: {{$item['price'] * (int)$TourInfo['tpx']}},
                },
              @endforeach
            ],
          @else
            carts: [],
          @endif
          cartsCount: 0,
          message: '',
          days: 0,
          amount: 0,
          addToCart(idValue, titleValue, typeValue, priceValue, numPrice){
            const item = { id: idValue, title: titleValue, type: typeValue, price: priceValue, nprice: parseFloat(numPrice * {{(int)$TourInfo['tpx']}}) };
            if(!this.carts.find(cartItem => cartItem.id == idValue)){
                this.carts.push(item);
                this.alert = true;
                this.alertType = 'success';
                this.message = `${titleValue} added from the cart!`;
            }
            else{
                this.alert = true;
                this.alertType = 'error';
                this.message = `${titleValue} was already added`;
            }
          },
          removeItemCart(id, title, numPrice) {
              const index = this.carts.findIndex(cartItem => cartItem.id == id);
              if (index !== -1) {
                this.carts.splice(index, 1);
                this.alert = true;
                this.alertType = 'success';
                this.message = `${title} was removed from the cart!`;
              } 
              else {
                this.alert = true;
                this.alertType = 'error';
                this.message = `${title} was already remove`;
              }
          },
          computeAmount(){
            this.amount = 0;
            for (let x in this.carts) {
              this.amount += parseFloat(this.carts[x].nprice);
            }
            this.days = this.carts.length;
          }
        }"
        @if(session()->has('erinfo') && !empty(session()->get('erinfo')['tm']) && !empty($tourListCart))
          x-init="days = carts.length;"
        @endif
        x-cloak>
        <x-loader />
    <div x-show="alert" id="close" class="fixed left-0 top-0 flex justify-center z-[100] w-full" x-effect="setTimeout(() => { alert = false, alertType = '', message = '' }, 5000)">
        <div :class="alertType == 'error' ? 'alert-error' : 'alert-success'" class="alert shadow-md">
            <i :class="alertType == 'error' ? 'fa-solid fa-circle-exclamation' : 'fa-solid fa-circle-check'" class="text-xl"></i>        
            <div>
                <span class="text-md font-semibold" x-text="message"></span>
            </div>
            <button x-on:click="alert = false" class=" btn btn-sm md:btn-circle btn-ghost">
                <i class="hidden md:inline fa-solid fa-xmark text-md"></i>
                <span class="inline md:hidden">CLOSE</span>
            </button>
        </div>
    </div>
    <div class="mx-auto max-w-screen-md md:flex flex-col justify-center px-4 py-8 sm:px-6 sm:py-12 lg:px-8" >
      <div class="flex justify-center item- pb-10 text-center ">
        <ul class="w-full steps steps-horizontal">
          <li data-content="✓" class="step step-primary">Dates</li>
          <li class="step step-primary">Tour Menu</li>
          <li class="step">Confirmation</li>
        </ul>
      </div>
      <header>
        <h2 class="text-xl font-bold text-gray-900 sm:text-3xl">
          Tour Menu
        </h2>

        <p class="mt-4 max-w-md font-bold text-2xl">
          1. Fill up Service Information
        </p>
      </header>
      <div class="mt-8 block lg:hidden">
        <button x-on:click="filterOpen = ! filterOpen" type="button" class="flex cursor-pointer items-center gap-2 border-b border-gray-400 pb-1 text-gray-900 transition hover:border-gray-600">
          <span class="text-sm font-medium">Filters</span>
          <i :class="filterOpen ? 'fa-solid fa-chevron-down' : 'fa-solid fa-greater-than'"></i>          
        </button>
      </div>
        <form x-data="{at: '{{$TourInfo['at']}}'}" action="{{route('user.reservation.edit.step21.store', $id)}}" method="post">
        @csrf
        <div class="mt-4 ">
          <div :class="filterOpen ? 'transition-all duration-1000 ease-in-out' : 'hidden' " class="space-y-4 lg:block">
            @if(request()->has(['py', 'tpx',]))
              <div class="opacity-50" id="disabledAll">
            @else
              <div>
            @endif
                <x-select xModel="at" name="accommodation_type" id="accommodation_type" placeholder="Accommodation Type" :value="$arrAccType" :title="$arrAccTypeTitle" selected="{{$TourInfo['at']}}" />
                <x-datetime-picker name="check_in" id="check_in" placeholder="Check in" class="flatpickr-reservation" value="{{$TourInfo['cin']}}"/>
                <div x-show="!(at == 'Day Tour' )" x-transaction>
                    <x-datetime-picker name="check_out" id="check_out" placeholder="Check out" class="flatpickr-reservation" value="{{$TourInfo['cout'] }}" />
                </div>
                {{-- Number of Guest --}}
                <x-input type="number" name="pax" id="pax" placeholder="Number of Guests" value="{{$TourInfo['px']}}"/>
                <template x-if="at === 'Day Tour' || at === 'Overnight'">
                  <x-input type="number" name="tour_pax" id="tour_pax" placeholder="How many people will be going on the tour" value="{{$TourInfo['tpx'] ?? ''}}" />
                </template>
                {{-- Payment Method  --}}
                <x-select id="payment_method" name="payment_method" placeholder="Payment Method" :value="$arrPayment"  :title="$arrPayment" selected="{{$TourInfo['py']}}"/>
                @if(request()->has(['py', 'tpx']) && $TourInfo['at'] != 'Room Type')
                    <div class="hidden">
                @else
                    <div class="lg:flex justify-start gap-5">
                @endif
                    <div class="mt-8 flex gap-4">
                      <a href="{{route('user.reservation.edit.step1', $id)}}" class="btn btn-ghost">Back</a>
                      <button @click="loader = true" type="submit" class="btn btn-primary">Continue</button>
                    </div>
                  </div>
            </div>

          </div>
        </div>
      </form>
        <div class="divider"></div>
        @if(request()->has(['py', 'tpx']) && $TourInfo['at'] != 'Room Type')
          <div id="tourmenu" class="w-full">
            <header>
              <p class="mt-4 max-w-md font-bold text-2xl">
                2. Choose your menu wisely
              </p>
            </header>
            <div class="mt-5">
              <h4 class="text-sm font-semibold">Days: <span class="font-normal">{{$user_days}}</span></h4>
              <h4 class="text-sm font-semibold">You Guest will going on tour: <span class="font-normal">{{$TourInfo['tpx']}} guest</span></h4>
            </div>
            <h4 class="mt-5 text-sm font-semibold text-error">Note: Each tour is equivalent to 1 day</h4>

            <form id="tour-form" action="{{route('user.reservation.edit.step22.store', $id)}}" method="post">
              @csrf
              <div class="flex justify-between mt-5">
                <h3 class="font-bold text-xl">Category</h3>
                <div class="dropdown dropdown-end">
                  <label tabindex="0" class="btn btn-ghost btn-circle">
                    <div class="indicator">
                      <i class="fa-solid fa-cart-shopping text-lg"></i>
                      <span class="badge badge-sm indicator-item" x-text="carts.length"></span>
                    </div>
                  </label>
                  <div tabindex="0" class="card p-6 w-96 md:w-[50rem] compact dropdown-content z-[1] shadow-md bg-base-100 rounded-box ">
                    <div class="card-body">
                      <h2 class="card-title">My Cart</h2> 
                      <div class="overflow-x-auto">
                        <table id="cart" class="table table-zebra">
                          <!-- head -->
                          <thead>
                            <tr>
                              <th>Tour</th>
                              <th>Type</th>
                              <th>Price</th>
                              <th>Quantity</th>
                              <th></th>
                            </tr>
                          </thead>
                          <tbody>
  
                            <!-- row -->
                            <template x-for="(cart, index) in carts" :key="index">
                              <tr x-effect="computeAmount()">
                                <td x-text="cart.title"></td>
                                <td x-text="cart.type"></td>
                                <td x-text="cart.price"></td>
                                <td>{{(int)$TourInfo['tpx']}}</td>
                                <td>
                                  <label :for="'removeCart' + index" class="btn btn-ghost">
                                    <i class="fa-solid fa-x text-xl text-error"></i>
                                  </label>
                                  <input type="checkbox" :id="'removeCart' + index" class="modal-toggle" />
                                  <div class="modal">
                                    <div class="modal-box">
                                      <h3 class="font-bold text-lg">Do you want to remove: <span x-text="cart.title"></span></h3>
                                      <div class="modal-action">
                                        <label :for="'removeCart' + index" @click="removeItemCart(cart.id, cart.title, cart.nprice)" class="btn btn-primary">Yes</label>
                                        <label :for="'removeCart' + index" class="btn btn-ghost">No</label>
                                      </div>
                                    </div>
                                  </div>
                                </td>
                                  <input x-ref="idRef + (index + 1)" type="hidden" name="tour_menu[]", :value="$el.value = cart.id">
                              </tr>
          
                            </template>

                            <template x-if="carts.length === 0">
                              <tr class="w-full"><td colspan="4" class="font-bold w-full text-center">No Cart Item!</td></tr>
                            </template>
                          
                          </tbody>
                        </table>
                      </div>
                      <template x-if="carts.length !== 0">
                        <div class="flex justify-start">
                          <div class="font-bold mr-3">Total: </div>
                          <div x-text="amount > 0 ? '₱ ' + amount.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></div>
                        </div>
                      </template>
                    </div>
                  </div>
                </div>
              </div>
            </form>
            <x-tour :tourCategory="$tour_category" :tourLists="$tour_lists" tpx="{{$TourInfo['tpx']}}" atpermit="{{$TourInfo['at'] == 'Day Tour' ? 1 : 0 }}" noDays="{{$user_days}}" />
          <div class="flex justify-start gap-5">
            <div class="mt-8 flex gap-4">
              <a href="{{route('user.reservation.edit.step2', $id)}}" class="btn btn-ghost">Back</a>
              <button @click="loader = true" onclick="event.preventDefault(); document.getElementById('tour-form').submit();" class="btn btn-primary">Next</button>
            </div>
          </div>
        @endif

      </form>
    </div>
  </section>

  @php 
    $from = App\Models\WebContent::all()->first()->from ?? null; 
    $to = App\Models\WebContent::all()->first()->to ?? null; 
  @endphp

  @push('scripts')
      <script>
        @if(isset($from) && isset($to))
          const mop = {
              from: '{{\Carbon\Carbon::createFromFormat('Y-m-d', $from)->format('Y-m-d')}}',
              to: '{{\Carbon\Carbon::createFromFormat('Y-m-d', $to)->format('Y-m-d')}}'
          };
        @else
          const mop = '2001-15-30';
        @endif
        const md = '{{Carbon\Carbon::now()->addDays(2)->format('Y-m-d')}}';
        document.getElementById('tourmenu').scrollIntoView(); // Scroll to the element with the provided ID
      </script>
      <script type="module" src="{{Vite::asset('resources/js/flatpickr.js')}}"></script>

  @endpush
</x-landing-layout>