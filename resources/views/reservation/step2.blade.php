@php
    $arrAccType = ['Room Only', 'Day Tour', 'Overnight'];
    $arrPayment = ['Gcash', 'PayPal', 'Bank Transfer'];
    $TourInfo = [];
    $tourListCart = [];
    if(session()->has('rinfo')){
      $decrypted = decryptedArray(session('rinfo'));
      if(isset($decrypted['tm'])){
        $tourListCart = $decrypted['tm'];
      }
      $TourInfo = [
        "cin" => old('check_in') ?? $decrypted['cin'],
        "cout" => old('check_out') ?? $decrypted['cout'],
        "px" => old('pax') ?? $decrypted['px'],
        "tpx" => old('tour_pax') ?? (request()->has('tpx') ? decrypt(request('tpx')) : $decrypted['tpx']  ),
        "at" => old('accommodation_type') ?? $decrypted['at'],
        "py" =>  old('payment_method') ?? $decrypted['py'],
        "ck" => request('ck') ?? '',
      ];
    }
    else{
      $TourInfo = [
        "cin" => request()->has('cin') ? old('check_in') ?? decrypt(request('cin')) : old('check_in'),
        "cout" => request()->has('cout') ? old('check_out') ?? decrypt(request('cout')) : old('check_out'),
        "px" => request()->has('px') ? old('pax') ?? decrypt(request('px')) : old('pax'),
        "tpx" => request()->has('tpx') ? decrypt(request('tpx')) : old('tour_pax'),
        "at" => request()->has('at') ? old('accommodation_type') ?? decrypt(request('at')) : old('accommodation_type'),
        "py" => request()->has('py') ? old('payment_method') ?? decrypt(request('py')) : old('payment_method'),
      ];
    }
    // dd( $TourInfo);
@endphp

<x-landing-layout noFooter>
  <section class="w-full"
        x-data="{
          @if(request()->has(['cin', 'cout', 'px', 'py', 'tpx','at']) && $TourInfo['at'] != 'Room Type')
            filterOpen: false, 
          @else
            filterOpen: true, 
          @endif
          alert: false,
          alertType: '',
          loader: false,
          @if(session()->has('rinfo') && !empty(session()->get('rinfo')['tm']))
            carts: [
              @foreach($tourListCart as $key => $item)
                { id: '{{ $tourListCart[$key] ?? '' }}', 
                  title: '{{ $cmenu[$key]['title'] ?? '' }}',
                  type: '{{ $cmenu[$key]['type'] ?? '' }} ({{$cmenu[$key]['pax']}} guest)',
                  price: 'P {{ number_format($cmenu[$key]['price'], 2) ?? '' }}',
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
        <form x-data="{at: '{{$TourInfo['at']}}'}" action="{{route('reservation.choose.check.one')}}" method="post">
        @csrf
        <div class="mt-4 ">
          <div :class="filterOpen ? 'transition-all duration-1000 ease-in-out' : 'hidden' " class="space-y-4 lg:block">
            @if(request()->has(['cin', 'cout', 'px', 'py', 'tpx','at']))
              <div class="opacity-50" id="disabledAll">
            @else
              <div>
            @endif
                <x-datetime-picker name="check_in" id="check_in" placeholder="Check in" class="flatpickr-reservation" value="{{$TourInfo['cin']}}"/>
                <x-datetime-picker name="check_out" id="check_out" placeholder="Check out" class="flatpickr-reservation" value="{{$TourInfo['cout'] }}" />
                <x-select xModel="at" name="accommodation_type" id="accommodation_type" placeholder="Accommodation Type" :value="$arrAccType" :title="$arrAccType" selected="{{$TourInfo['at']}}" />
                {{-- Number of Guest --}}
                <x-input type="number" name="pax" id="pax" placeholder="Number of Guests" value="{{$TourInfo['px']}}"/>
                <template x-if="at === 'Day Tour' || at === 'Overnight'">
                  <x-input type="number" name="tour_pax" id="tour_pax" placeholder="How many people will be going on the tour" value="{{$TourInfo['tpx'] ?? ''}}" />
                </template>
                {{-- Payment Method  --}}
                <x-select id="payment_method" name="payment_method" placeholder="Payment Method" :value="$arrPayment"  :title="$arrPayment" selected="{{$TourInfo['py']}}"/>
                @if(request()->has(['cin', 'cout', 'px', 'py', 'tpx','at']) && $TourInfo['at'] != 'Room Type')
                    <div class="hidden">
                @else
                    <div class="lg:flex justify-start gap-5">
                @endif
                    <div class="mt-8 flex gap-4">
                      <a href="{{route('reservation.date', Arr::query(['cin' => $TourInfo['cin'], 'cout' => $TourInfo['cout'], 'at' => $TourInfo['at'], 'px' => $TourInfo['px']]))}}" class="btn btn-ghost">Back</a>
                      <button @click="loader = true" type="submit" class="btn btn-primary">Continue</button>
                    </div>
                  </div>
            </div>

          </div>
        </div>
      </form>
        <div class="divider"></div>
        @if(request()->has(['cin', 'cout', 'px', 'py', 'tpx','at']) && $TourInfo['at'] != 'Room Type')
          <div id="tourmenu" class="w-full">
            <header>
              <p class="mt-4 max-w-md font-bold text-2xl">
                2. Choose your menu wisely
              </p>
            </header>
            <div class="gap-10">
              <form id="tour-form" action="{{route('reservation.choose.check.all', Arr::query(encryptedArray($TourInfo)))}}" method="post">
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
                  <div tabindex="0" class="card p-6 w-96 md:w-[50rem] compact dropdown-content z-[1] drop-shadow-lg border bg-base-100 rounded-box ">
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
              <h4 class="mb-3 text-sm font-semibold">You Guest will going on tour: <span class="font-normal">{{$TourInfo['tpx']}} guest</span></h4>
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
                          {{-- Card List Tour --}}
                          @foreach ($tour_lists as $list)
                            @php $list_count = $loop->index + 1 ?? 1; @endphp

                            @if ($category->category === $list->category)
                              @if($user_days < $list->no_day)
                                  <div class="h-auto opacity-70" id="disabledAll">   
                              @else
                                <div class="h-auto">
                              @endif
                                  <div class="card my-3 w-96 bg-base-100 shadow-xl border border-primary hover:border-primary hover:bg-primary hover:text-base-100">
                                    <label for="{{$user_days < $list->no_day ? 'disabledAll' : Str::camel($list->title)}}" tabindex="0">
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
                                    <x-modal id="{{$user_days < $list->no_day ? 'disabledAll' : Str::camel($list->title)}}" title="{{$list->title}}" alpinevar="price">
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
                                                  @if($TourInfo['tpx'] > $menu->pax && $menu_count === count($list->tourMenuLists))
                                                  <div class="w-full h-full">
                                                    <input id="{{$TourInfo['tpx'] > $menu->pax ? Str::camel($menu->type). '_' . $list->id : 'disabledAll' }}" class="peer hidden [&:checked_+_label_i]:block" type="radio" value="{{$menu->id}}"  x-model="price" />
                                                    <label for="{{$TourInfo['tpx'] > $menu->pax ? Str::camel($menu->type). '_' . $list->id : 'disabledAll' }}" :aria-checked="price == '{{$menu->price}}'" :class="price == '{{$menu->price}}' ? 'mr-5 relative border-primary ring-1 ring-primary' : 'mr-5'" for="{{Str::replace(' ', '_', Str::lower($menu->type))}}" class="block cursor-pointer rounded-lg border border-base-100 bg-base-100 p-4 text-sm font-medium shadow-sm hover:border-base-200 ">
                                                  @else
                                                  <div id="{{$TourInfo['tpx'] != $menu->pax ? 'disabledAll' : ''}}" class="w-full h-full">
                                                    <input id="{{$TourInfo['tpx'] == $menu->pax ? Str::camel($menu->type). '_' . $list->id : 'disabledAll' }}" class="peer hidden [&:checked_+_label_i]:block" type="radio" value="{{$menu->id}}"  x-model="price" />
                                                    <label for="{{$TourInfo['tpx'] == $menu->pax ? Str::camel($menu->type). '_' . $list->id : 'disabledAll' }}" :aria-checked="price == '{{$menu->price}}'" :class="price == '{{$menu->price}}' ? 'mr-5 relative border-primary ring-1 ring-primary' : 'mr-5'" for="{{Str::replace(' ', '_', Str::lower($menu->type))}}" class="block cursor-pointer rounded-lg border border-base-100 bg-base-100 p-4 text-sm font-medium shadow-sm hover:border-base-200 ">
                                                  @endif
                                                @else
                                                  <div class="w-full h-full">
                                                    <input id="{{$TourInfo['tpx'] >= $menu->pax ? Str::camel($menu->type). '_' . $list->id : 'disabledAll' }}" class="peer hidden [&:checked_+_label_i]:block" type="radio" value="{{$menu->id}}"  x-model="price" />
                                                    <label for="{{$TourInfo['tpx'] >= $menu->pax ? Str::camel($menu->type). '_' . $list->id : 'disabledAll' }}" :aria-checked="price == '{{$menu->price}}'" :class="price == '{{$menu->price}}' ? 'mr-5 relative border-primary ring-1 ring-primary' : 'mr-5'" for="{{Str::replace(' ', '_', Str::lower($menu->type))}}" class="block cursor-pointer rounded-lg border border-base-100 bg-base-100 p-4 text-sm font-medium shadow-sm hover:border-base-200 ">
                                                @endif
                                                      <div class="flex items-center justify-between">
                                                        <p class="text-neutral" x-ref="refType{{$menu->id}}">{{$menu->type}} ({{$menu->pax}} guest)</p>
                                                        <i class="hidden text-primary fa-solid fa-square-check"></i>
                                                      </div>
                                                      <p class="mt-1 text-neutral" x-ref="priceRef{{$menu->id}}">P {{number_format($menu->price, 2)}}</p>
                                                      @if(count($list->tourMenuLists) !== 1)
                                                        @if($TourInfo['tpx'] > $menu->pax && $menu_count !== count($list->tourMenuLists))
                                                            <p class="absolute text-error text-xs">Invalid guest count for this price.</p>
                                                        @endif
                                                      @endif
                                                      @if(count($list->tourMenuLists) !== 1)
                                                        @if($TourInfo['tpx'] < $menu->pax)
                                                            <p class="absolute text-error text-xs">Invalid guest count for this price.</p>
                                                        @endif
                                                      @endif
                                                    </label>
                                                  </div>
                                            @endforeach
                                      </div>
                                      @foreach ($list->tourMenuLists as $menu)

                                          <template x-if="price == {{$menu->id}}">
                                            <label for="{{$user_days < $list->no_day ? '' : Str::camel($list->title)}}" @click=" addToCart(price, $refs.titleRef{{$list_count}}.innerText, $refs.refType{{$menu->id}}.innerText, $refs.priceRef{{$menu->id}}.innerText)" class="btn btn-primary float-right">
                                              Add to Cart
                                            </label>
                                          </template>
                                      @endforeach
                                    </x-modal>
                                  </div>
                                </div>
                            @endif
                          @endforeach
                      </div>
                    </div>
                @endforeach
          </div>
          <div class="flex justify-start gap-5">
            <div class="mt-8 flex gap-4">
              <a href="{{route('reservation.choose', Arr::query(["cin" =>  request('cin'), "cout" =>  request('cout'), "px" =>  request('px'), "at" =>  request('at')]) ) }}" class="btn btn-ghost">Back</a>
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
  @endpush
</x-landing-layout>