@php
    $addonsIDS = [];
    $addonsTitle = [];
    foreach ($addons_list as $value) {
        $addonsIDS[] = encrypt($value->id);
        $addonsTitle[] = $value->title . ' - ₱ ' .number_format($value->price, 2);
    }
@endphp
<x-system-layout :activeSb="$activeSb">
    <x-system-content title="" back="{{route('system.reservation.show', encrypt($r_list->id))}}"
      >
        {{-- User Details --}}
        <div class="w-full text-center">
            <span x-show="!document.querySelector('[x-cloak]')" class="loading loading-spinner loading-lg text-primary"></span>
        </div>
        <section
            x-data="{
                alert: false,
                alertType: '',
                carts: [],
                cartsCount: 0,
                message: '',
                days: 0,
                amount: 0,
                addToCart(idValue, titleValue, typeValue, priceValue, numPrice){
                  const item = { id: idValue, title: titleValue, type: typeValue, price: priceValue, nprice: parseFloat(numPrice * {{(int)request('gpax') ?? 1}}) };
                  if(!this.carts.find(cartItem => cartItem.id == idValue)){
                      this.carts.push(item);
                      this.amount += numPrice;
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
                      this.amount -= numPrice;
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

                },
            }"
            x-cloak
        >
        <div x-show="alert" id="close" class="fixed left-0 top-0 flex justify-center z-[100] w-full" x-effect="setTimeout(() => { alert = false }, 5000)">
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
        <x-profile :rlist="$r_list" />
        <div class="flex justify-between items-center space-x-1 px-20">
            <div>
              @if(request()->has('tab') && request('tab') === 'TA')
                <div class="text-neutral"><strong>Number of guest will be going to tour: </strong>{{$r_list->tour_pax}} pax</div>
              @else
                <div class="text-neutral"><strong>Number of Pax: </strong>{{$r_list->pax}} pax</div>
              @endif
            </div>
            <div class="tabs tabs-boxed bg-transparent items-center">
                <a href="{{route('system.reservation.show.addons', [encrypt($r_list->id), 'tab=TA'])}}" class="tab {{request()->has('tab') && request('tab') === 'TA' ? 'tab-active' : ''}}">Tour Addons</a> 
                <a href="{{route('system.reservation.show.addons', encrypt($r_list->id) )}}" class="tab {{request()->has('tab') && request('tab') === 'TA' ? '' : 'tab-active'}}">Other Addons</a> 
            </div>
        </div>
        <div class="divider"></div>
        @if(request()->has('tab') && request('tab') === 'TA')
          <article class="block w-full px-14">
            <div class="w-full">
                <div class="gap-10">
                  <div class="flex gap-2">
                    <h3 class="font-bold text-xl">Category</h3>
                    <div class="join">

                    </div>
                    <form action="{{ route('system.reservation.show.addons', ['id' => encrypt($r_list->id)]) }}" method="get">
                      <div class="join">
                          <div>
                              <input type="number" name="gpax" placeholder="Number of Guests..." class="input input-bordered input-primary input-sm join-item" max="{{ $r_list->pax }}" value="{{ request('gpax') ?? '' }}" />
                              @error('new_pax')
                              <span class="label-text-alt">{{ $message }}</span>
                              @enderror
                          </div>
                          <input type="hidden" name="tab" value="TA">
                          <button class="btn join-item btn-sm btn-primary">Proceed</button>
                      </div>
                  </form>
                  
                  </div>
                  @if(request('gpax'))

                    <form id="tour-form" action="{{route('system.reservation.addons.update', [encrypt($r_list->id), 'tab=TA'])}}" method="post">
                      @csrf
                      @method('PUT')
                      <div class="flex justify-between mt-6">
                        <div class="mt-5">
                          <h4 class="text-sm font-semibold">Days: <span class="font-normal">{{$r_list->getNoDays()}}</span></h4>
                          <h4 class="text-sm font-semibold">You Guest will going on tour: <span class="font-normal">{{request('gpax')}} guest</span></h4>
                          <h4 class="mb-5 mt-3 text-sm font-semibold text-error">Note: Each tour is equivalent to 1 day</h4>
                        </div>

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
                                      <th></th>
                                    </tr>
                                  </thead>
                                  <tbody>
          
                                    <!-- row -->
                                    <template x-for="(cart, index) in carts" :key="index">
                                      <tr x-effect="amount += cart.nprice">
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
                      <input type="hidden" name="new_pax"  value="{{request('gpax') ?? null}}">
                      <x-tour :tourCategory="$tour_category" :tourLists="$tour_lists" tpx="{{request('gpax')}}" atpermit="0" noDays="{{1000}}" />
                      <x-passcode-modal title="Tour Add-ons Confirmation" id="tourmdl" formId="tour-form" />
                      <div class="flex justify-end    ">
                        <label for="tourmdl" class="btn btn-primary">Add</label>
                      </div>
                    </form >
                  @else
                    <h3 class="font-bold text-xl text-center my-10">Fill the number guest will be going tour</h3>
                  @endif
              </div>
          </article>
        @else
          <article class="w-full flex justify-center px-20">
            <form id="other-form" action="{{route('system.reservation.addons.update', encrypt($r_list->id))}}" method="POST" class="w-96">
                @csrf
                @method('PUT')
                <h2 class="text-xl md:text-2xl mb-5 font-bold">Add-ons</h2>
                <x-select name="addons" id="addons" placeholder="Add-on" :value="$addonsIDS" :title="$addonsTitle" selected="{{$addonsTitle[old('addons')] ?? ''}}" />
                <x-input name="pcs" id="pcs" placeholder="How many pcs" value="{{old('pcs') ?? ''}}" max="{{$r_list->pax}}" /> 
                <x-passcode-modal title="Other Add-ons Confirmation" id="other_modal" formId="other-form" />
                <label for="other_modal" class="btn btn-primary btn-block">Add</label>
            </form>
          </article>
        @endif

    </section>
    </x-system-content>
</x-system-layout>