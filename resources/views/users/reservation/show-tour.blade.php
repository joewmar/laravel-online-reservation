@php


@endphp

<x-landing-layout noFooter>
    <x-navbar :activeNav="$activeNav" type="plain"/>
    <x-full-content>
        <section 
            x-data="{
              alert: false,
              alertType: '',
              @forelse($cmenu ?? [] as $item)
                carts: [
                  {
                    id: '{{ $item['id'] ?? '' }}', 
                    title: '{{ $item['title'] ?? '' }}',
                    price: '₱ {{ number_format($item['price'], 2) ?? '' }}',
                    amount: '₱ {{ number_format($item['amount'], 2) ?? '' }}',
                  },
                ],
              @empty
                carts: [],
              @endforelse
              cartsCount: 0,
              message: '',
              addToCart(idValue, titleValue, priceValue, amountValue){
              const item = { id: idValue, title: titleValue, price: priceValue, amount: amountValue };
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
          x-cloak
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
            <a href="{{URL::previous()}}" class="btn btn-ghost btn-circle">
              <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="px-3 md:px-20">
                <div class="w-full sm:flex sm:space-x-6">          
                    <div class="flex flex-col space-y-4">
                        <div>
                            <h2 class="text-2xl font-semibold">{{$r_list->userReservation->name()}}</h2>
                            <span class="block text-sm text-neutral">{{$r_list->userReservation->age()}} years old from {{$r_list->userReservation->country}}</span>
                            <span class="text-sm text-neutral">{{$r_list->userReservation->nationality}}</span>
                        </div>
                        <div class="space-y-1">
                            <span class="flex items-center space-x-2">
                                <i class="fa-regular fa-envelope w-4 h-4"></i>
                                <span class="text-neutral">{{$r_list->userReservation->email}}</span>
                            </span>
                            <span class="flex items-center space-x-2">
                                <i class="fa-solid fa-phone w-4 h-4"></i>
                                <span class="text-neutral">{{$r_list->userReservation->contact}}</span>
                            </span>
                        </div>
                    </div>

                </div>
                <div class="divider"></div>
                <article class="block w-full">
                    <div class="w-full">
                        <div class="gap-10">
                          <div class="flex gap-2">
                            <h3 class="font-bold text-xl">Category</h3>
                            <div class="join">
        
                            </div>
                            <form action="{{ route('system.reservation.show.addons', ['id' => encrypt($r_list->id)]) }}" method="get">
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
        
                            <form id="edit_tour_form" action="{{route('user.reservation.update.tour', encrypt($r_list->id))}}" method="post">
                              @csrf
                              @method('PUT')
                              <div class="flex justify-between mt-6">
                                <h4 class="mb-3 text-sm font-semibold">You Guest will going on tour: <span class="font-normal">{{request('gpax')}} guest</span></h4>
        
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
                                        <table id="cart" class="table">
                                          <!-- head -->
                                          <thead>
                                            <tr>
                                              <th>Tour</th>
                                              <th>Price</th>
                                              <th>Amount</th>
                                              <th></th>
                                            </tr>
                                          </thead>
                                          <tbody>
                  
                                            <!-- row -->
                                            <template x-for="(cart, index) in carts" :key="index">
                                              <tr>
                                                <td class="w-full md:w-96" x-text="cart.title"></td>
                                                <td x-text="cart.price"></td>
                                                <td x-text="cart.amount"></td>
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
                              <input type="hidden" name="new_pax"  value="{{request('gpax') ?? null}}">
                              <div x-data="{category: null}" x-cloak>
                                  @foreach ($tour_category as $category)
                                    @if($loop->index === 0)
                                      <input x-model="category" id="{{Str::replace(' ', '_', Str::lower($category->category))}}" class="my-2 radio radio-primary" x-model="category" type="radio" value="{{Str::camel($category->category)}}" />
                                      <label x-init="category = '{{Str::camel($category->category)}}'" :aria-checked="category == '{{Str::camel($category->category)}}'" :class="category == '{{Str::camel($category->category)}}' ? 'mr-5 text-primary' : 'mr-5'" for="{{Str::replace(' ', '_', Str::lower($category->category))}}" class="my-5">{{$category->category}}</label>  
                                    @else
                                        <input x-model="category" id="{{Str::replace(' ', '_', Str::lower($category->category))}}" class="my-2 radio radio-primary" x-model="category" type="radio" value="{{Str::camel($category->category)}}"/>
                                        <label :aria-checked="category == '{{Str::camel($category->category)}}'" :class="category == '{{Str::camel($category->category)}}' ? 'mr-3 text-primary' : 'mr-3'" for="{{Str::replace(' ', '_', Str::lower($category->category))}}" class="my-5">{{$category->category}}</label>  
                                    @endif
                        
                                  @endforeach
                      
                                @foreach ($tour_category as $category)
                                  @php $category_count = $loop->index + 1; @endphp
                                  <div class="w-full" x-cloak>
                                      <div class="grid grid-cols-1 md:grid-cols-3 place-items-center" x-data="{ price: '' }" :class="category == '{{Str::camel($category->category)}}' ? 'block' : 'hidden'">
                                        {{-- Card List Tour --}}
                                        @foreach ($tour_lists as $list)
                                          @php $list_count = $loop->index + 1 ?? 1; @endphp
        
                                          @if ($category->category === $list->category)
                                              <div class="place-self-stretch card my-3 w-80 bg-base-100 shadow-xl border border-primary hover:border-primary hover:bg-primary hover:text-base-100">
                                                <label for="{{Str::camel($list->title)}}" tabindex="0">
                                                    <div class="card-body">
                                                      <h2 x-ref="titleRef{{$list_count}}" class="card-title">{{$list->title}} </h2> 
                                                    </div>
                                                </label>
                                                {{-- Modal Tour Details --}}                                  
                                                <x-modal id="{{Str::camel($list->title)}}" title="{{$list->title}}" alpinevar="price">
                                                  <article>
                                                    <ul role="list" class="marker:text-primary list-disc pl-5 space-y-3 text-neutral">
                                                      <li><strong>Number of days: </strong> {{$list->no_day <= 1 ? $list->no_day . ' day' : $list->no_day . ' days' }}</li>
                                                      <li><strong>Price Plan</strong></li>
                                                    </ul>
                                                  </article>
                                                  <div class="grid gap-4 grid-cols-2 my-4">
                                                      @foreach ($list->tourMenuLists as $menu)
                                                          @php 
                                                            $menu_count = $loop->index + 1; 
                                                          @endphp
                                                            @if(count($list->tourMenuLists) != 1)
                                                              @if(request('gpax') > $menu->pax && $menu_count === count($list->tourMenuLists))
                                                              <div class="w-full h-full">
                                                                <input id="{{request('gpax') > $menu->pax ? Str::camel($menu->type). '_' . $list->id : 'disabledAll' }}" class="peer hidden [&:checked_+_label_i]:block" type="radio" value="{{$menu->id}}"  x-model="price" />
                                                                <label for="{{request('gpax') > $menu->pax ? Str::camel($menu->type). '_' . $list->id : 'disabledAll' }}" :aria-checked="price == '{{$menu->id}}'" :class="price == '{{$menu->id}}' ? 'mr-5 relative border-primary ring-1 ring-primary' : 'mr-5'" for="{{Str::replace(' ', '_', Str::lower($menu->type))}}" class="block cursor-pointer rounded-lg border border-base-100 bg-base-100 p-4 text-sm font-medium shadow-sm hover:border-base-200 ">
                                                              @else
                                                              <div id="{{request('gpax') != $menu->pax ? 'disabledAll' : ''}}" class="w-full h-full">
                                                                <input id="{{request('gpax') == $menu->pax ? Str::camel($menu->type). '_' . $list->id : 'disabledAll' }}" class="peer hidden [&:checked_+_label_i]:block" type="radio" value="{{$menu->id}}"  x-model="price" />
                                                                <label for="{{request('gpax') == $menu->pax ? Str::camel($menu->type). '_' . $list->id : 'disabledAll' }}" :aria-checked="price == '{{$menu->id}}'" :class="price == '{{$menu->id}}' ? 'mr-5 relative border-primary ring-1 ring-primary' : 'mr-5'" for="{{Str::replace(' ', '_', Str::lower($menu->type))}}" class="block cursor-pointer rounded-lg border border-base-100 bg-base-100 p-4 text-sm font-medium shadow-sm hover:border-base-200 ">
                                                              @endif
                                                            @else
                                                              <div class="w-full h-full">
                                                                <input id="{{request('gpax') >= $menu->pax ? Str::camel($menu->type). '_' . $list->id : 'disabledAll' }}" class="peer hidden [&:checked_+_label_i]:block" type="radio" value="{{$menu->id}}"  x-model="price" />
                                                                <label for="{{request('gpax') >= $menu->pax ? Str::camel($menu->type). '_' . $list->id : 'disabledAll' }}" :aria-checked="price == '{{$menu->id}}'" :class="price == '{{$menu->id}}' ? 'mr-5 relative border-primary ring-1 ring-primary' : 'mr-5'" for="{{Str::replace(' ', '_', Str::lower($menu->type))}}" class="block cursor-pointer rounded-lg border border-base-100 bg-base-100 p-4 text-sm font-medium shadow-sm hover:border-base-200 ">
                                                            @endif
                                                                  <div class="flex items-center justify-between">
                                                                    <p class="text-neutral" x-ref="refType{{$menu->id}}">{{$menu->type}} ({{$menu->pax}} guest)</p>
                                                                    <i class="hidden text-primary fa-solid fa-square-check"></i>
                                                                  </div>
                                                                  <p class="mt-1 text-neutral" x-ref="priceRef{{$menu->id}}">P {{number_format($menu->price, 2)}}</p>
                                                                  @if(count($list->tourMenuLists) !== 1)
                                                                    @if(request('gpax') > $menu->pax && $menu_count !== count($list->tourMenuLists))
                                                                        <p class="absolute text-error text-xs">Invalid guest count for this price.</p>
                                                                    @endif
                                                                  @endif
                                                                  @if(count($list->tourMenuLists) !== 1)
                                                                    @if(request('gpax') < $menu->pax)
                                                                        <p class="absolute text-error text-xs">Invalid guest count for this price.</p>
                                                                    @endif
                                                                  @endif
                                                                </label>
                                                              </div>
      
                                                        @endforeach
                                                  </div>
                                                    @foreach ($list->tourMenuLists as $menu)
                                                      <template x-if="price == {{$menu->id}}">
                                                        <label for="{{Str::camel($list->title)}}" @click=" addToCart(price, $refs.titleRef{{$list_count}}.innerText, $refs.priceRef{{$menu->id}}.innerText, '₱ {{number_format(($menu->price * request('gpax') ?? 1),2)}}')" class="btn btn-primary float-right">
                                                          Add to Cart
                                                        </label>
                                                      </template>
                                                    @endforeach
                                                  </x-modal>
                                              </div>
                                          @endif
                                        @endforeach
                                    </div>
                                  </div>
                                @endforeach
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