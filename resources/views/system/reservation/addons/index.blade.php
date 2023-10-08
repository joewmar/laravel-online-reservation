@php
    $addonsIDS = [];
    $addonsTitle = [];
    foreach ($addons_list as $value) {
        $addonsIDS[] = encrypt($value->id);
        $addonsTitle[] = $value->title . ' - ₱ ' .number_format($value->price, 2);
    }
@endphp
<x-system-layout :activeSb="$activeSb">
    <x-system-content title="" back=true>
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
        <div class="w-full py-8 sm:flex sm:space-x-6 px-20">
            <div class="hidden md:flex flex-shrink-0 mb-6 h-15 sm:h-32 w-15 sm:w-32 sm:mb-0">
                @if(filter_var($r_list->userReservation->avatar ?? '', FILTER_VALIDATE_URL))
                    <img src="{{$r_list->userReservation->avatar}}" alt="" class="object-cover object-center w-full h-full rounded">
                @elseif($r_list->userReservation->avatar ?? false)
                    <img src="{{asset('storage/'. $r_list->userReservation->avatar)}}" alt="" class="object-cover object-center w-full h-full rounded">
                @else
                    <img src="{{asset('images/avatars/no-avatar.png')}}" alt="" class="object-cover object-center w-full h-full rounded">
                @endif
            </div>            
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
          
                                    <!-- row -->
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
                      <input type="hidden" name="new_pax"  value="{{request('gpax') ?? null}}">
                      <div x-data="{category: null}" x-cloak>
                          @foreach ($tour_category as $category)
                            @if($loop->index === 0)
                              <input x-model="category" id="{{Str::replace(' ', '_', Str::lower($category->category))}}" class="my-2 radio radio-primary" x-model="category" type="radio" value="{{Str::camel($category->category)}}" />
                              <label class="text-sm md:text-lg" x-init="category = '{{Str::camel($category->category)}}'" :aria-checked="category == '{{Str::camel($category->category)}}'" :class="category == '{{Str::camel($category->category)}}' ? 'mr-2 text-primary' : 'mr-2'" for="{{Str::replace(' ', '_', Str::lower($category->category))}}" class="my-5">{{$category->category}}</label>  
                            @else
                                <input x-model="category" id="{{Str::replace(' ', '_', Str::lower($category->category))}}" class="my-2 radio radio-primary" x-model="category" type="radio" value="{{Str::camel($category->category)}}"/>
                                <label class="text-sm md:text-lg" :aria-checked="category == '{{Str::camel($category->category)}}'" :class="category == '{{Str::camel($category->category)}}' ? 'mr-2 text-primary' : 'mr-2'" for="{{Str::replace(' ', '_', Str::lower($category->category))}}" class="my-5">{{$category->category}}</label>  
                            @endif
                
                          @endforeach
              
                        @foreach ($tour_category as $category)
                          @php $category_count = $loop->index + 1; @endphp
                          <div x-data="{ price: '' }" :class="category == '{{Str::camel($category->category)}}' ? 'grid grid-cols-1 md:grid-cols-3 place-items-center' : 'hidden'">
                            {{-- Card List Tour --}}
                            @foreach ($tour_lists as $list)
                              @php $list_count = $loop->index + 1 ?? 1; @endphp

                              @if ($category->category === $list->category)
                                    <div class="place-self-stretch card my-3 w-72 bg-base-100 shadow-xl border border-primary hover:border-primary hover:bg-primary hover:text-base-100">
                                      <label for="{{Str::camel($list->title)}}" tabindex="0">
                                          <div class="card-body">
                                            <h2 x-ref="titleRef{{$list_count}}" class="card-title">{{$list->title}} </h2> 
                                            @if($user_days < $list->no_day)
                                              <p class="text-error">You are not allowed to select this menu if the chosen date is not exact</p>
                                              <p class="text-error">Your days: {{$user_days}} day/s</p>
                                              <p class="text-error">Tour duration: {{$list->no_day}} day/s only</p>
                                            @endif
                                          </div>
                                      </label>
                                      {{-- Modal Tour Details --}}                                  
                                      <x-modal id="{{Str::camel($list->title)}}" title="{{$list->title}}" alpinevar="price" noBottom>
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
                                              <label for="{{Str::camel($list->title)}}"@click=" addToCart(price, $refs.titleRef{{$list_count}}.innerText, $refs.refType{{$menu->id}}.innerText, $refs.priceRef{{$menu->id}}.innerText)" class="btn btn-primary float-right">
                                                Add to Cart
                                              </label>
                                            </template>
                                          @endforeach

                                        </x-modal>
                                    </div>
                                
                              @endif
                            @endforeach
                          </div>
                        @endforeach
                        <div class="flex justify-end md:justify-start">
                          <label for="tour_modal" class="btn btn-primary">Add Tour</label>
                          <x-passcode-modal title="Tour Add-ons Confirmation" id="tour_modal" formId="tour-form" />
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
                <h2 class="text-2xl mb-5 font-bold">Add-ons</h2>
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