@push('styles')
  <link rel="stylesheet" href="{{Vite::asset("resources/css/users/menu-slider.css")}}" />    
@endpush
@php
    $arrPayment = ['Walk-in', 'Gcash', 'Paymaya'];
    $arrAccType = ['All', 'Day Tour', 'Overnight'];
@endphp

<x-landing-layout>

<section
      x-data="{
        filterOpen: false, 
        alert: false,
        alertType: '',
        priceNum: '', 
        title: '', 
        carts: [],
        cartsCount: 0,
        message: '',
        addToCart(id, title, price){
          const item = { id, title, price };
          if(!this.carts.find(cartItem => cartItem.id === id)){
            this.carts.push(item);
            this.alert = true;
            this.alertType = 'success';
            this.message = `${title} added from the cart!`;
          }
          else{
            this.alert = true;
            this.alertType = 'error';
            this.message = `${title} was already added`;
          }
        },
        removeItemCart(id, title){
          const index = this.carts.findIndex(cartItem => cartItem.id === id);
          if (index !== -1) {
            this.carts.splice(index, 1);
            this.alert = true;
            this.message = `${title} removed from the cart!`;
          } 
          else {
            this.alert = true;
            this.message = 'Item not found in the cart!';
          }
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
        <li data-content="✓" class="step step-primary">Dates</li>
        <li class="step step-primary">Tour Menu</li>
        <li class="step">Details</li>
        <li class="step">Confirmation</li>
      </ul>
    </div>
    <header>
      <h2 class="text-xl font-bold text-gray-900 sm:text-3xl">
        Tour Menu
      </h2>

      <p class="mt-4 max-w-md font-bold text-2xl">
        1. Fill up this First
      </p>
    </header>
    <div class="mt-8 block lg:hidden">
      <button x-on:click="filterOpen = ! filterOpen" type="button" class="flex cursor-pointer items-center gap-2 border-b border-gray-400 pb-1 text-gray-900 transition hover:border-gray-600">
        <span class="text-sm font-medium">Filters</span>
        <i :class="filterOpen ? 'fa-solid fa-chevron-down' : 'fa-solid fa-greater-than'"></i>          
      </button>
    </div>

    <form action="{{route('reservation.choose.check.one')}}" method="post">
      @csrf
      <div class="mt-4 ">
        <div :class="filterOpen ? 'transition-all duration-1000 ease-in-out' : 'hidden' " class="space-y-4 lg:block">
          @if(request()->has(['cin', 'cout', 'px', 'at', 'py']))
            <div class="opacity-50" id="disabledAll">
          @else
            <div>
          @endif
              <x-datetime-picker name="check_in" id="check_in" placeholder="Check in" class="flatpickr-reservation input-sm " value="{{$cin === null ? '' : $cin}}" />
              <x-datetime-picker name="check_out" id="check_out" placeholder="Check out" class="flatpickr-reservation flatpickr-input2 input-sm "  value="{{$cout === null ? '' : $cout}}"/>
              {{-- Number of Guest --}}
              <x-input type="number" name="pax" id="pax" placeholder="Number of Guests" value="{{$px === null ? '' : $px}}"/>
              {{-- Accommodations Type --}}
              <x-select id="accommodation_type" name="accommodation_type" placeholder="Accommodations Type" :value="$arrAccType"  val="{{$at === null ? '' : $at}}"/>
              {{-- Payment Method  --}}
              <x-select id="payment_method" name="payment_method" placeholder="Payment Method" :value="$arrPayment"  val="{{$py === null ? '' : $py}}"/>
              @if(request()->has(['cin', 'cout', 'px', 'at', 'py']))
                  <div class="hidden">
              @else
                  <div class="lg:flex justify-start gap-5">
              @endif
                  <div class="mt-8 flex gap-4">
                    <a href="{{route('reservation.date')}}" class="btn btn-ghost">Back</a>
                    <button type="submit" class="btn btn-primary">Continue</button>
                  </div>
                </div>
          </div>

        </div>
      </div>
      <div class="divider"></div>
      @if(request()->has(['cin', 'cout', 'px', 'at', 'py']))
        <div id="tourmenu" class="w-full">
          <header>
            <p class="mt-4 max-w-md font-bold text-2xl">
              2. Choose your menu wisely
            </p>
          </header>
          <div class="gap-10">
            
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
                          <template x-for="cart in carts">
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
    
            <div x-data="{category: null}" x-cloak>
              @foreach ($tour_category as $category)
                @if($loop->index === 0)
                  <input x-model="category" id="{{Str::replace(' ', '_', Str::lower($category->category))}}" class="my-2 radio radio-primary" x-model="category" type="radio" name="category" value="{{Str::camel($category->category)}}" />
                  <label x-init="category = '{{Str::camel($category->category)}}'" :aria-checked="category == '{{Str::camel($category->category)}}'" :class="category == '{{Str::camel($category->category)}}' ? 'mr-5 text-primary' : 'mr-5'" for="{{Str::replace(' ', '_', Str::lower($category->category))}}" class="my-5">{{$category->category}}</label>  
                @else
                    <input x-model="category" id="{{Str::replace(' ', '_', Str::lower($category->category))}}" class="my-2 radio radio-primary" x-model="category" type="radio" name="category" value="{{Str::camel($category->category)}}"/>
                    <label :aria-checked="category == '{{Str::camel($category->category)}}'" :class="category == '{{Str::camel($category->category)}}' ? 'mr-5 text-primary' : 'mr-5'" for="{{Str::replace(' ', '_', Str::lower($category->category))}}" class="my-5">{{$category->category}}</label>  
                @endif
    
              @endforeach
    
              @foreach ($tour_category as $category)
                  <div class="flex w-full" x-cloak>
                      <div x-data="{ price: '' }" :class="category == '{{Str::camel($category->category)}}' ? 'block' : 'hidden'">
                        {{-- Card List Tour --}}
                        @foreach ($tour_lists as $list)
                          @if ($category->category === $list->category)
                            @if($user_days <= $list->no_day)
                                <div class="h-auto opacity-70" id="disabledAll">   
                            @else
                              <div class="h-auto">
                            @endif
                                <div class="card my-3 w-96 bg-base-100 shadow-xl border border-primary hover:border-primary hover:bg-primary hover:text-base-100">
                                  <label for="{{$user_days <= $list->no_day ? 'disabledAll' : Str::camel($list->title)}}" tabindex="0">
                                      <div class="card-body">
                                        <h2 x-ref="titleRef" x-model="title" class="card-title">{{$list->title}}</h2> 
                                        @if($user_days <= $list->no_day)
                                          <p class="text-error">You are not allowed to select this menu if the chosen date is not exact</p>
                                          <p class="text-error">Your days: {{$user_days}} day/s</p>
                                          <p class="text-error">Tour duration: {{$list->no_day}} day/s only</p>
                                        @endif
                                      </div>
                                  </label>
                                  {{-- Modal Tour Details --}}                                  
                                  <x-modal id="{{$user_days <= $list->no_day ? 'disabledAll' : Str::camel($list->title)}}" title="{{$list->title}}" alpinevar="price">
                                    <article>
                                      <ul role="list" class="marker:text-primary list-disc pl-5 space-y-3 text-slate-500">
                                        <li><strong>Number of days: </strong> {{$list->no_day <= 1 ? $list->no_day . ' day' : $list->no_day . ' days' }}</li>
                                        <li><strong>Number of hour/s: </strong> {{Str::replace('.', ' hour and ', $list->hrs) . ' minutes'}}</li>
                                        <li><strong>Price Plan</strong></li>
                                      </ul>
                                    </article>
                                    <div class="grid gap-4 grid-cols-2 my-4">
                                        @foreach ($tour_menus as $menu)
                                            @if($menu->menu_id === $list->id)
                                                  <div class="w-full h-full">
                                                    <input id="{{$px == $menu->pax ? Str::camel($menu->type). '_' . $list->id : 'disabledAll' }}" name="{{$list->id}}_price" class="peer hidden [&:checked_+_label_i]:block" type="radio" value="{{$list->id . '_' . $menu->id}}"  x-model="price" />
                                                    <label for="{{$px == $menu->pax ? Str::camel($menu->type). '_' . $list->id : 'disabledAll' }}" :aria-checked="price == '{{$menu->price}}'" :class="price == '{{$menu->price}}' ? 'mr-5 relative border-primary ring-1 ring-primary' : 'mr-5'" for="{{Str::replace(' ', '_', Str::lower($menu->type))}}" class="block cursor-pointer rounded-lg border border-base-100 bg-base-100 p-4 text-sm font-medium shadow-sm hover:border-base-200 ">
                                                      <div class="flex items-center justify-between">
                                                        <p class="text-neutral">{{$menu->type}} ({{$menu->pax}} pax)</p>
                                                        <i class="hidden text-primary fa-solid fa-square-check"></i>
                                                      </div>
                                                      <p class="mt-1 text-neutral" x-ref="priceRef" x-model="priceNum">P {{number_format($menu->price, 2)}}</p>
                                                      @if($px != $menu->pax)
                                                        <p class="absolute text-error text-xs">Invalid guest count for this price.</p>
                                                      @endif
                                                    </label>
                                                  </div>
                                            @endif
                                          @endforeach
                                    </div>
                                    <template x-if="price">
                                      <label for="{{$user_days <= $list->no_day ? '' : Str::camel($list->title)}}"
                                      @click=" addToCart(price, $refs.titleRef.innerText, $refs.priceRef.innerText)" 
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
            <a href="{{route('reservation.choose', Arr::query(["cin" =>  request('cin'), "cout" =>  request('cout')]) ) }}" class="btn btn-ghost">Back</a>
            <button type="submit" class="btn btn-primary">Next</button>
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
      document.getElementById("disabledAll").disabled = true;
      var nodes = document.getElementById("disabledAll").getElementsByTagName('*');
      for(var i = 0; i < nodes.length; i++){
          nodes[i].disabled = true;
      }
    </script>
@endpush
</x-landing-layout>