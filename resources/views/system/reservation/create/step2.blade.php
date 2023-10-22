@php
    $TourInfo = [
        "rt" => request()->has('rt') ? request('rt') : null,
        "rm" => request()->has('rm') ? request('rm') : null,
        "cin" => request()->has('cin') ? decrypt(request('cin')) : old('check_in'),
        "cout" => request()->has('cout') ? decrypt(request('cout')) : old('check_out'),
        "px" => request()->has('px') ? decrypt(request('px')) : old('pax'),
        "tpx" => request()->has('tpx') ? decrypt(request('tpx')) : old('tour_pax'),
        "at" => request()->has('at') ? decrypt(request('at')) : old('accommodation_type'),
        "py" => request()->has('py') ? decrypt(request('py')) : old('payment_method'),
        "st" => request()->has('st') ? decrypt(request('st')) :  old('status'),
      ];
    $TourInfoEncrypted = [
        "rt" => request()->has('rt') ? request('rt') : null,
        "rm" => request()->has('rm') ? request('rm') : null,
        "cin" => request()->has('cin') ? request('cin') : old('check_in'),
        "cout" => request()->has('cout') ? request('cout') : old('check_out'),
        "px" => request()->has('px') ? request('px') : old('pax'),
        "tpx" => request()->has('tpx') ? request('tpx') : old('tour_pax'),
        "at" => request()->has('at') ? request('at') : old('accommodation_type'),
        "py" => request()->has('py') ? request('py') : old('payment_method'),
        "st" => request()->has('st') ? request('st') :  old('status'),
      ];
      

    if(session()->has('nwrinfo')){
      $decrypted = decryptedArray(session('nwrinfo'));
      $TourInfo = [
        "rt" => request()->has('rt') ? request('rt') : null,
        "rm" => request()->has('rm') ? request('rm') : null,
        "cin" => $decrypted['cin'] ?? old('check_in'),
        "cout" => $decrypted['cout'] ?? old('check_out'),
        "px" => $decrypted['px'] ?? old('pax'),
        "tpx" => $decrypted['tpx'] ?? old('tour_pax'),
        "at" => $decrypted['at'] ?? old('accommodation_type'),
        "py" => $decrypted['py'] ?? old('payment_method'),
        "st" => $decrypted['st'] ??  old('status'),
      ];
    }

@endphp

<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Add Book (Tour)">
    <section
      x-data="{
        filterOpen: false, 
        alert: false,
        alertType: '',
        loader: false,
         @if(session()->has('nwrinfo') && !empty(session()->get('nwrinfo')['tm']))
          carts: [
            @foreach($cmenu as $key => $item)
              { id: '{{ $item['id'] ?? '' }}', 
                title: '{{ $item['title'] ?? '' }}',
                type: '{{ $item['type'] ?? '' }} ({{$item['pax']}} guest)',
                price: '₱ {{ number_format($item['price'], 2) ?? '' }}',
              },
            @endforeach
          ],
        @else
          carts: [],
        @endif
        cartsCount: 0,
        message: '',
        addToCart(idValue, titleValue, typeValue, priceValue){
          const item = { id: idValue, title: titleValue, type: typeValue, price: priceValue };
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
        removeItemCart(id, title) {
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
      }"
      x-cloak>
      <x-loader />
      <div x-show="alert" id="close" class="fixed left-0 top-0 flex justify-center z-[100] w-full" x-init="console.log(carts); setTimeout(() => { alert = false }, 5000)">
        <div :class="alertType == 'error' ? 'alert-error' : 'alert-success'" class="w-full alert shadow-md">
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
          @if(request()->has(['cin', 'cout', 'py', 'at', 'st', 'tpx']) && $TourInfo['at'] != 'Room Type')
            <div id="tourmenu" class="w-full">
              <header>
                <p class="mt-4 max-w-md font-bold text-2xl">
                  Choose your Tour
                </p>
              </header>
              <h4 class="mt-3 text-sm font-semibold">You Guest will going on tour: <span class="font-normal">{{$TourInfo['tpx']}} guest</span></h4>

              <div class="gap-10">
                <form id="tour-form" action="{{route('system.reservation.store.step.two-two', Arr::query($TourInfoEncrypted))}}" method="post">
                  @csrf
                <div class="flex justify-between mt-6">
                  <h3 class="font-bold text-lg">Category</h3>
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
                                <th>Menu</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th></th>
                              </tr>
                            </thead>
                            <tbody>
                              <template x-for="(cart, index) in carts" :key="index">
                                <tr>
                                  <td x-text="cart.title"></td>
                                  <td x-text="cart.type"></td>
                                  <td x-text="cart.price"></td>
                                  <td>
                                    <label :for="'removeCart' + index" class="btn btn-ghost">
                                      <i class="fa-solid fa-x text-xl text-error"></i>
                                    </label>
                                    <input type="checkbox" :id="'removeCart' + index" class="modal-toggle" />
                                    <div class="modal">
                                      <div class="modal-box">
                                        <h3 class="font-bold text-lg">Do you want to remove: <span x-text="cart.title"></span></h3>
                                        <div class="modal-action">
                                          <label :for="'removeCart' + index" @click="removeItemCart(cart.id, cart.title)" class="btn btn-primary">Yes</label>
                                          <label :for="'removeCart' + index" class="btn btn-ghost">No</label>
                                        </div>
                                      </div>
                                    </div>
                                  </td>
                                    <input x-ref="idRef + (index + 1)" type="hidden" name="tour_menu[]", :value="$el.value = cart.id">
                                </tr>
                              </template>
                              <template x-if="carts.length === 0">
                                <tr colspan="2" class="w-full"><td class="font-bold w-full text-center">No Cart Item!</td></tr>
                              </template>
                            
                            </tbody>
                          </table>
                        </div>
        
                      </div>
                    </div>
                  </div>
                </div>

              </form>
              <div class="w-full text-center">
                  <span x-show="!document.querySelector('[x-cloak]')" class="loading loading-spinner loading-lg text-primary"></span>
              </div>
              <x-tour :tourCategory="$tour_category" :tourLists="$tour_lists" tpx="{{$TourInfo['tpx']}}" atpermit="{{$TourInfo['at'] == 'Day Tour' ? 1 : 0 }}" />

            <div class="flex justify-start gap-5">
              <div class="mt-8 flex gap-4">
                  <a @click="loader = true" href="{{route('system.reservation.create.step.one', Arr::query($TourInfoEncrypted) ) }}" class="btn btn-ghost">Back</a>
                  <button @click="loader = true" onclick="event.preventDefault(); document.getElementById('tour-form').submit();" class="btn btn-primary">Next</button>
              </div>
            </div>
          @endif
        </form>
      </div>
    </section>
      @push('scripts')
          <script>
            // Scroll to the element with the provided ID
            document.getElementById('tourmenu').scrollIntoView();
          </script>
          <script type="module" src="{{Vite::asset('resources/js/flatpickr2.js')}}"></script>
      @endpush
  </x-system-content>
</x-system-layout>