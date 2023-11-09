
<x-landing-layout noFooter>
    <x-navbar :activeNav="$activeNav" type="plain"/>
    <x-full-content>
        <section 
            x-data="{
              alert: false,
              alertType: '',
              carts: [
              @forelse($cmenu ?? [] as $item)
                  {
                    id: '{{ $item['id'] ?? '' }}', 
                    title: '{{ $item['title'] ?? '' }}',
                    price: '₱ {{ number_format($item['price'], 2) ?? '' }}',
                    nprice: parseFloat({{$item['price'] * (int)request('gpax') ?? 1}}),
                  },
                
              @empty

              @endforelse
              ],
              cartsCount: 0,
              message: '',
              days: 0,
              amount: 0,
              addToCart(idValue, titleValue, typeValue, priceValue, numPrice){
                const item = { id: idValue, title: titleValue, type: typeValue, price: priceValue, nprice: parseFloat(numPrice * {{(int)request('gpax') ?? 1}}) };
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
              },
          }"
          x-cloak
          @if(!empty($cmenu ))
            x-init="days = carts.length;"
          @endif
          class="px-10 md:px-20 pt-24">
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
            {{-- User Details --}}
            <a href="{{route('user.reservation.show', encrypt($r_list->id))}}" class="btn btn-ghost btn-circle">
              <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="px-3 md:px-20">
                <x-profile :rlist="$r_list" noPic />
                <div class="divider"></div>
                <article class="block w-full">
                    <div class="w-full">
                        <div class="gap-10">
                          <div class="flex gap-2">
                            <h3 class="font-bold text-xl">Category</h3>
                            <form action="{{ route('user.reservation.edit.tour', ['id' => encrypt($r_list->id)]) }}" method="get">
                              <div class="join">
                                  <div>
                                      <input type="number" name="gpax" placeholder="Number of Guests..." class="input input-bordered input-primary input-sm join-item" max="{{ $r_list->pax }}" value="{{ request('gpax') ?? $r_list->tour_pax }}" />
                                      @error('new_pax')
                                        <span class="label-text-alt">{{ $message }}</span>
                                      @enderror
                                  </div>
                                  <input type="hidden" name="tab" value="TA">
                                  <button class="btn join-item btn-sm btn-primary">Select</button>
                              </div>
                          </form>
                          
                          </div>
                          @if(request('gpax'))
        
                            <form x-init="if({{$r_list->tour_pax != request('gpax') ? 'true' : 'false'}}) carts = []" id="edit_tour_form" action="{{route('user.reservation.update.tour', encrypt($r_list->id))}}" method="post">
                              @csrf
                              @method('PUT')
                              <div class="flex justify-between mt-6">
                              </header>
                              <div class="mt-5">
                                <h4 class="text-sm font-semibold">Days: <span class="font-normal">{{$user_days}}</span></h4>
                                <h4 class="text-sm font-semibold">You Guest will going on tour: <span class="font-normal">{{request('gpax') ?? 'No'}} guest</span></h4>
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
                                                <td x-text="cart.price"></td>
                                                <td >{{request('gpax') ?? 'None'}}</td>
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
                              <x-tour :tourCategory="$tour_category" :tourLists="$tour_lists" tpx="{{request('gpax')}}" atpermit="{{$r_list->accommodation_type == 'Day Tour' ? 1 : 0 }}" noDays="{{$user_days}}" />
                                <div class="flex justify-end mb-5">
                                  <label for="tour_modal" class="btn btn-primary">Save</label>
                                  <x-modal title="Do you want to save?" type="YesNo" formID="edit_tour_form" id="tour_modal">
                                  </x-modal>
                              </div>
                            </form >
                          @else
                            <h3 class="font-bold text-xl text-center my-10">Fill the number guest will be going tour</h3>
                          @endif
                      </div>
                  </article>
            </div>
        </section>
    </x-full-content>
</x-landing-layout>