@push('styles')
  <link rel="stylesheet" href="{{Vite::asset("resources/css/users/menu-slider.css")}}" />    
@endpush
@php
    $arrPayment = ['Walk-in', 'Gcash', 'Paymaya'];
    $arrAccType = ['All', 'Day Tour', 'Overnight'];
    $reserveMenu = null;
    if (request()->has(['cin', 'cout', 'px', 'at', 'py'])){
        $reserveMenu = [
          "cin" =>  decrypt(request('cin')),
          "cout" => decrypt(request('cout')),
          "px" => decrypt(request('px')),
          "at" =>   decrypt(request('at')) ,
          "py" =>  decrypt(request('py')),
        ];
    }
    elseif(request()->has(['cin', 'cout'])){
        $reserveMenu = [
          "cin" =>  decrypt(request('cin')),
          "cout" => decrypt(request('cout')),
          "px" => '',
          "at" =>   '' ,
          "py" =>  '',
        ];
    }
@endphp

<x-landing-layout>

<section>
  <div class="mx-auto max-w-screen-md md:flex flex-col justify-center px-4 py-8 sm:px-6 sm:py-12 lg:px-8" x-data="{filterOpen: false, pax: '', dateStart: '', dateEnd: '', carts: []}">
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

      <p class="mt-4 max-w-md font-bold">
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
              <x-datetime-picker name="check_in" id="check_in" placeholder="Check in" class="flatpickr-reservation input-sm " value="{{$reserveMenu === null ? '' : $reserveMenu['cin']}}" />
              <x-datetime-picker name="check_out" id="check_out" placeholder="Check out" class="flatpickr-reservation flatpickr-input2 input-sm "  value="{{$reserveMenu === null ? '' : $reserveMenu['cout']}}"/>
              {{-- Number of Guest --}}
              <x-input type="number" name="pax" id="pax" placeholder="Number of Guests" value="{{$reserveMenu === null ? '' : $reserveMenu['px']}}"/>
              {{-- Accommodations Type --}}
              <x-select id="accommodation_type" name="accommodation_type" placeholder="Accommodations Type" :value="$arrAccType"  val="{{$reserveMenu === null ? '' : $reserveMenu['at']}}"/>
              {{-- Payment Method  --}}
              <x-select id="payment_method" name="payment_method" placeholder="Payment Method" :value="$arrPayment"  val="{{$reserveMenu === null ? '' : $reserveMenu['py']}}"/>
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
            <p class="mt-4 max-w-md font-bold">
              2. Choose your menu wisely
            </p>
          </header>
          <div class="gap-10">
            
            <div class="flex justify-between mb-3">
              <h3 class="font-bold text-xl">Category</h3>
              <div class="dropdown dropdown-end">
                <label tabindex="0" class="btn btn-ghost ">
                  <i class="fa-solid fa-cart-shopping text-lg"></i>
                </label>
                <div tabindex="0" class="card w-96 compact dropdown-content z-[1] shadow bg-base-100 rounded-box ">
                  <div class="card-body">
                    <h2 class="card-title">My Cart</h2> 
                    <div class="overflow-x-auto">
                      <table id="cart" class="table table-zebra">
                        <!-- head -->
                        <thead>
                          <tr>
                            <th>Menu</th>
                            <th>Price</th>
                          </tr>
                        </thead>
                        <tbody>
                          <!-- row -->
                          {{-- <template x-for="cart in carts">
                            <tr>
                              <th x-text="cart.title"></th>
                              <td x-text="cart.price"></td>
                            </tr>
                          </template> --}}
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
                              <div class="h-auto">
                                <div class="card my-3 w-96 bg-base-100 shadow-xl border border-primary peer-checked:border-primary peer-checked:bg-primary peer-checked:text-base-100">
                                  {{-- <div class="absolute flex justify-center items-center w-full h-full">
                                    <span class="-100">Sorry sample!</span>
                                  </div> --}}
                                  <label for="{{Str::camel($list->title)}}" tabindex="0">
                                      <div class="card-body" >
                                        <div class="flex justify-between">
                                          <h2 class="card-title">{{$list->title}}</h2> 
                                        </div>
                                      </div>
                                  </label>
                                  {{-- Modal Tour Details --}}
                                  <x-modal id="{{Str::camel($list->title)}}" title="{{$list->title}}" alpinevar="price">
                                    <article>
                                      <ul role="list" class="marker:text-primary list-disc pl-5 space-y-3 text-slate-500">
                                        <li><strong>Number of days: </strong> {{$list->no_day <= 1 ? $list->no_day . ' day' : $list->no_day . ' days' }}</li>
                                        <li><strong>Number of hour/s: </strong> {{Str::replace('.', ' hour and ', $list->hrs) . ' minutes'}}</li>
                                        <li><strong>Price Plan</li>
                                      </ul>
                                    </article>
                                    <div class="grid gap-4 grid-cols-2 my-4">
                                        @foreach ($tour_menus as $menu)
                                            @if($menu->menu_id === $list->id)
                                                  <div x-init="$el.className = '' " :class="pax == {{$menu->pax}}? 'block' : 'hidden'">
                                                    <input id="{{Str::camel($menu->type)}}_{{$list->id}}" class="peer hidden [&:checked_+_label_i]:block" type="radio" value="{{Str::camel($menu->price)}}"  x-model="price" />
                                                    <label for="{{Str::camel($menu->type)}}_{{$list->id}}" :aria-checked="price == '{{$menu->price}}'" :class="price == '{{$menu->price}}' ? 'mr-5 border-primary ring-1 ring-primary' : 'mr-5'" for="{{Str::replace(' ', '_', Str::lower($menu->type))}}" class="block cursor-pointer rounded-lg border border-base-100 bg-base-100 p-4 text-sm font-medium shadow-sm hover:border-base-200 ">
                                                      <div class="flex items-center justify-between">
                                                        <p class="text-gray-700">{{$menu->type}}</p>
                                                        <i class="hidden text-primary fa-solid fa-square-check"></i>
                                                      </div>
                                                      <p class="mt-1 text-neutral">P {{number_format($menu->price, 2)}}</p>
                                                    </label>
                                                  </div>
                                            @endif
                                          @endforeach
                                    </div>
                                    <template x-if="price">
                                      <label class="btn btn-primary float-right">Add to Cart</label>
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