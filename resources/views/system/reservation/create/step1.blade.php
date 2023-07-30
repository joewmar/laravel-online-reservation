@php
    $arrAccType = ['Room Only', 'Day Tour', 'Overnight'];
    $arrPayment = ['Walk-in', 'Gcash', 'Paymaya'];
    // $reserve = [];
    $TourInfo = [];
    if(request()->has(['dy', 'px', 'at'])){
      $TourInfo = [
        "dy" => decrypt(request('dy')) ?? '',
        "px" => decrypt(request('px')) ?? '',
        "at" => decrypt(request('at')) ?? '',
      ];
    }
    // if(request()->has(['dy', 'px', 'at'])){

    // }
    // if(session()->has('rinfo')){
    //   if(decrypt(session('rinfo')['at']) !== 'Room Only'){
    //     $tourList = explode(',', decrypt(session('rinfo')['tm']));
    //   }
    // }
@endphp

<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Add Book">

    <section
          x-data="{
            filterOpen: false, 
            alert: false,
            alertType: '',
            {{-- @if(session()->has('rinfo'))
              carts: [
                @foreach($tourList as $key => $item)
                  { id: '{{ $tourList[$key] ?? '' }}', 
                    title: '{{ $cmenu[$key]['title'] ?? '' }}',
                    price: '{{ number_format($cmenu[$key]['price'], 2) ?? '' }}',
                  },
                @endforeach
              ], --}}
            {{-- @else --}}
              carts: [],
            {{-- @endif --}}
            cartsCount: 0,
            message: '',
            addToCart(idValue, titleValue, priceValue){
              const item = { id: idValue, title: titleValue, price: priceValue };
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
              console.log(this.carts);

            },
            removeItemCart(id, title){
              const index = this.carts.findIndex(cartItem => cartItem.id == id);
              if (index !== -1) {
                this.carts.splice(index, 1);
                this.alert = true;
                this.message = `${title} removed from the cart!`;
              } 
              else {
                this.alert = true;
                this.message = 'Item not found in the cart!';
              }
              console.log(this.carts);

            },
          }"
          x-cloak>
      <div x-show="alert" id="close" class="fixed top-0 flex justify-center z-[100] w-full" x-init="setTimeout(() => { alert = false }, 5000)">
        <div :class="alertType == 'error' ? 'alert-error' : 'alert-success'" class="w-96 alert shadow-md">
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
            <li class="step step-primary">Tour Details</li>
            <li class="step">Details</li>
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
          <form action="{{route('system.reservation.store.step.one')}}" method="post">
          @csrf
          <div class="mt-4 ">
            <div :class="filterOpen ? 'transition-all duration-1000 ease-in-out' : 'hidden' " class="space-y-4 lg:block">
              @if(request()->has(['dy', 'px', 'at']))
                <div class="opacity-50" id="disabledAll">
              @else
                <div>
              @endif
                  <x-input type="number" name="days" id="days" placeholder="No of days" value="{{ $TourInfo['dy'] ?? old('days')}}"/>
                  <x-select name="accommodation_type" id="accommodation_type" placeholder="Accommodation Type" :value="$arrAccType" :title="$arrAccType" selected="{{ $TourInfo['cin'] ?? old('accommodation_type')}}" />
                  {{-- Number of Guest --}}
                  <x-input type="number" name="pax" id="pax" placeholder="Number of Guests" value="{{ $TourInfo['px'] ?? old('pax')}}"/>
                  @if(request()->has(['dy', 'px', 'at']))
                      <div class="hidden">
                  @else
                      <div class="lg:flex justify-start gap-5">
                  @endif 
                      <div class="mt-8 flex gap-4">
                        <button type="submit" class="btn btn-primary">Next</button>
                      </div>
                    </div>
              </div>
            </div>
          </div>
        </form>
          <div class="divider"></div>
          @if(request()->has(['dy', 'px', 'at']))
            <div id="tourmenu" class="w-full">
              <header>
                <p class="mt-4 max-w-md font-bold text-2xl">
                  2. Choose the Room
                </p>
              </header>
              <div class="my-5">
                <form id="tour-form" action="{{route('reservation.choose.check.all', Arr::query($TourInfo))}}" method="post">
                  @csrf
                  <div class="w-full">
                    <div class="flex justify-between">
                      <div class="form-control w-full">
                        <label for="room_rate" class="w-full relative flex justify-start rounded-md border border-base-200 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary ">
                                <select name="room_rate" id="room_rate" class='w-full select select-primary peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0'>
                                <option value="" disabled selected>Please select</option>
                                @foreach ($rates as $key => $rate)
                                    @if(old('room_rate') == $rate->id);
                                        <option value="{{$rate->id}}" selected>{{$rate->name}} ({{$rate->occupancy}} pax)</option>
                                    @elseif($rate->occupancy == $TourInfo['px'])
                                        <option value="{{$rate->id}}" selected>{{$rate->name}} ({{$rate->occupancy}} pax)</option>
                                    @else
                                        <option value="{{$rate->id}}">{{$rate->name}} ({{$rate->occupancy}} pax)</option>
                                    @endif
                                @endforeach
                            </select>        
                            <span id="room_rate" class="pointer-events-none absolute start-2.5 top-0 -translate-y-1/2 bg-white p-0.5 text-xs text-neutral transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-0 peer-focus:text-xs">
                                Room Type
                            </span>
                        </label>
                        <label class="label">
                            <span class="label-text-alt">
                                @error('room_rate')
                                    <span class="label-text-alt text-error">{{$message}}</span>
                                @enderror
                            </span>
                        </label>
                      </div>                          
                      <div class="my-2 space-y-3">
                            <div class="dropdown dropdown-left">
                                <label tabindex="0" class="btn btn-ghost"><i class="fa-solid fa-circle-info"></i></label>
                                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                                    <li>                    
                                        <div class="flex items-center space-x-2">
                                            <label class="h-8 w-8 rounded-full bg-red-600 shadow-sm" ></label>
                                            <p class="font-medium">Reserved</p>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="flex items-center space-x-2">
                                            <label class="h-8 w-8 rounded-full bg-primary shadow-sm" ></label>
                                            <p class="font-medium">Available</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                      </div>
                    </div>
                    <div class="flex flex-wrap justify-center md:justify-normal flex-grow m-5 gap-5 w-full">
                        @forelse ($rooms as $key => $item)
                        <div id="{{$item->availability == 1 ? 'disabledAll' : 'none'}}">
                            @if($item->availability == 1)
                                <input type="checkbox" name="rooms[]" value="{{$item->id}}" id="{{$item->room_no}}" class="peer hidden [&:checked_+_label_span]:h-full [&:checked_+_label_span_h4]:block" disabled/>
                            @elseif($errors->has('rooms') && Arr::exists(old('rooms'), $item->id))
                                <input type="checkbox" name="rooms[]" value="{{$item->id}}" id="{{$item->room_no}}" class="peer hidden [&:checked_+_label_span]:h-full [&:checked_+_label_span_h4]:block" checked/>
                            @else
                                <input type="checkbox" name="rooms[]" value="{{$item->id}}" id="{{$item->room_no}}" class="peer hidden [&:checked_+_label_span]:h-full [&:checked_+_label_span_h4]:block" />
                            @endif
                            <label for="{{$item->room_no}}">
                                <div class="relative w-52 overflow-hidden rounded-lg border p-4 sm:p-6 lg:p-8 {{$item->availability == 1 ? 'opacity-70 bg-red-600' : 'border-primary cursor-pointer'}}">
                                    @if($item->availability == 1)
                                        <span class="absolute inset-x-0 bottom-0 h-full  bg-red-500 opacity-80 flex items-center"><h4 class="text-base-100 block font-medium w-full text-center">Reserved</h4></span>
                                    @else
                                        <span class="absolute inset-x-0 bottom-0 h-3 bg-primary flex items-center"><h4 class="text-primary-content hidden font-medium w-full text-center">Room No. {{$item->room_no}} Selected</h4></span>
                                    @endif
                                    <div class="sm:flex sm:justify-between sm:gap-4">
                                        <div>
                                            <h3 class="text-lg font-bold text-neutral sm:text-xl">Room No. {{$item->room_no}}</h3>
                                            <p class="mt-1 text-xs font-medium text-gray-600">{{$item->room->name}}</p>
                                            <p class="mt-1 text-xs font-medium text-gray-600">{{$item->room->min_occupancy}} up to {{$item->room->max_occupancy}} capacity</p>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                        @empty
                            <p class="text-2xl font-semibold">No Record Found</p>
                        @endforelse
                    </div>
                  </div>
                  @if(!request()->has(['dy', 'px', 'at']))
                      <div class="hidden">
                  @else
                      <div class="lg:flex justify-start gap-5">
                  @endif 
                        <div class="mt-8 flex gap-4">
                          <a href="{{route('system.reservation.create')}}" class="btn btn-ghost">Back</a>
                          <button type="submit" class="btn btn-primary">Next</button>
                        </div>
                </div>
              </form>
          @endif
          {{-- @if(request()->has(['cin', 'cout', 'px', 'py', 'at']) && $at != 'Room Type')
            <div id="tourmenu" class="w-full">
              <header>
                <p class="mt-4 max-w-md font-bold text-2xl">
                  2. Choose your menu wisely
                </p>
              </header>
              <div class="gap-10">
                <form id="tour-form" action="{{route('reservation.choose.check.all', Arr::query($TourInfo))}}" method="post">
                  @csrf
                <div class="flex justify-between mt-6">
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
                                <th>Menu</th>
                                <th>Price</th>
                                <th></th>
                              </tr>
                            </thead>
                            <tbody>

                              <!-- row -->
                              <template x-for="(cart, index) in carts" :key="index">
                                <tr>
                                  <td x-text="cart.title"></td>
                                  <td x-text="cart.price"></td>
                                  <td>
                                    <label for="removeCart" class="btn btn-ghost">
                                      <i class="fa-solid fa-x text-xl text-error"></i>
                                    </label>
                                    <input type="checkbox" id="removeCart" class="modal-toggle" />
                                    <div class="modal">
                                      <div class="modal-box">
                                        <h3 class="font-bold text-lg">Do you want to remove: <span x-text="cart.title"></span></h3>
                                        <div class="modal-action">
                                          <label for="removeCart" @click="removeItemCart(cart.id, cart.title)" class="btn btn-primary">Yes</label>
                                          <label for="removeCart" class="btn btn-ghost">No</label>
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
                <div x-data="{category: null}" x-cloak>
                  @foreach ($tour_category as $category)
                    @if($loop->index === 0)
                      <input x-model="category" id="{{Str::replace(' ', '_', Str::lower($category->category))}}" class="my-2 radio radio-primary" x-model="category" type="radio" value="{{Str::camel($category->category)}}" />
                      <label x-init="category = '{{Str::camel($category->category)}}'" :aria-checked="category == '{{Str::camel($category->category)}}'" :class="category == '{{Str::camel($category->category)}}' ? 'mr-5 text-primary' : 'mr-5'" for="{{Str::replace(' ', '_', Str::lower($category->category))}}" class="my-5">{{$category->category}}</label>  
                    @else
                        <input x-model="category" id="{{Str::replace(' ', '_', Str::lower($category->category))}}" class="my-2 radio radio-primary" x-model="category" type="radio" value="{{Str::camel($category->category)}}"/>
                        <label :aria-checked="category == '{{Str::camel($category->category)}}'" :class="category == '{{Str::camel($category->category)}}' ? 'mr-5 text-primary' : 'mr-5'" for="{{Str::replace(' ', '_', Str::lower($category->category))}}" class="my-5">{{$category->category}}</label>  
                    @endif
        
                  @endforeach
        
                  @foreach ($tour_category as $category)
                      @php $category_count = $loop->index + 1; @endphp
                      <div class="flex w-full" x-cloak>
                          <div x-data="{ price: '' }" :class="category == '{{Str::camel($category->category)}}' ? 'block' : 'hidden'">
                            {{-- Card List Tour 
                            @foreach ($tour_lists as $list)
                              @php $list_count = $loop->index + 1 ?? 1; @endphp

                              @if ($category->category === $list->category)
                                @if($user_days <= $list->no_day)
                                    <div class="h-auto opacity-70" id="disabledAll">   
                                @else
                                  <div class="h-auto">
                                @endif
                                    <div class="card my-3 w-96 bg-base-100 shadow-xl border border-primary hover:border-primary hover:bg-primary hover:text-base-100">
                                      <label for="{{$user_days <= $list->no_day ? 'disabledAll' : Str::camel($list->title)}}" tabindex="0">
                                          <div class="card-body">
                                            <h2 x-ref="titleRef{{$list_count}}" class="card-title">{{$list->title}} </h2> 
                                            @if($user_days <= $list->no_day)
                                              <p class="text-error">You are not allowed to select this menu if the chosen date is not exact</p>
                                              <p class="text-error">Your days: {{$user_days}} day/s</p>
                                              <p class="text-error">Tour duration: {{$list->no_day}} day/s only</p>
                                            @endif
                                          </div>
                                      </label>
                                      {{-- Modal Tour Details                               
                                      <x-modal id="{{$user_days <= $list->no_day ? 'disabledAll' : Str::camel($list->title)}}" title="{{$list->title}}" alpinevar="price">
                                        <article>
                                          <ul role="list" class="marker:text-primary list-disc pl-5 space-y-3 text-neutral">
                                            <li><strong>Number of days: </strong> {{$list->no_day <= 1 ? $list->no_day . ' day' : $list->no_day . ' days' }}</li>
                                            <li><strong>Number of hour/s: </strong> {{Str::replace('.', ' hour and ', $list->hrs) . ' minutes'}}</li>
                                            <li><strong>Price Plan</strong></li>
                                          </ul>
                                        </article>
                                        <div class="grid gap-4 grid-cols-2 my-4">
                                            @foreach ($tour_menus as $menu)
                                                @php $menu_count = $loop->index + 1; @endphp
                                                @if($menu->menu_id === $list->id)
                                                      <div class="w-full h-full">
                                                        <input id="{{!$px < $menu->pax ? Str::camel($menu->type). '_' . $list->id : 'disabledAll' }}" class="peer hidden [&:checked_+_label_i]:block" type="radio" value="{{$menu->id}}"  x-model="price" />
                                                        <label for="{{!$px < $menu->pax ? Str::camel($menu->type). '_' . $list->id : 'disabledAll' }}" :aria-checked="price == '{{$menu->price}}'" :class="price == '{{$menu->price}}' ? 'mr-5 relative border-primary ring-1 ring-primary' : 'mr-5'" for="{{Str::replace(' ', '_', Str::lower($menu->type))}}" class="block cursor-pointer rounded-lg border border-base-100 bg-base-100 p-4 text-sm font-medium shadow-sm hover:border-base-200 ">
                                                          <div class="flex items-center justify-between">
                                                            <p class="text-neutral">{{$menu->type}} ({{$menu->pax}} pax)</p>
                                                            <i class="hidden text-primary fa-solid fa-square-check"></i>
                                                          </div>
                                                          <p class="mt-1 text-neutral" x-ref="priceRef{{$list_count}}">P {{number_format($menu->price, 2)}}</p>
                                                          @if($px < $menu->pax)
                                                            <p class="absolute text-error text-xs">Invalid guest count for this price.</p>
                                                          @endif
                                                        </label>
                                                      </div>
                                                @endif
                                              @endforeach
                                        </div>
                                        <template x-if="price">
                                          <label for="{{$user_days <= $list->no_day ? '' : Str::camel($list->title)}}"
                                          @click=" addToCart(price, $refs.titleRef{{$list_count}}.innerText, $refs.priceRef{{$list_count}}.innerText)" 
                                          class="btn btn-primary float-right">Add to Cart</label>
                                        </template>
                                      </x-modal>
                                    </div>
                                  </div>
                              @endif
                            @endforeach
                        </div>
                      </div>
                  @endforeach
            </div>
            <div class="hidden lg:flex justify-start gap-5">
              <div class="mt-8 flex gap-4">
                <a href="{{route('reservation.choose', Arr::query(["cin" =>  request('cin'), "cout" =>  request('cout'), "px" =>  request('px'), "at" =>  request('at')]) ) }}" class="btn btn-ghost">Back</a>
                <button onclick="event.preventDefault(); document.getElementById('tour-form').submit();" class="btn btn-primary">Next</button>
              </div>
            </div>
          @endif --}}

        </form>
      </div>
    </section>

@push('scripts')
    <script>
      // Scroll to the element with the provided ID
      document.getElementById('tourmenu').scrollIntoView();
    </script>
@endpush
</x-system-content>
</x-system-layout>