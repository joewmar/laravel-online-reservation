@push('styles')
  <link rel="stylesheet" href="{{Vite::asset("resources/css/users/menu-slider.css")}}" />    
@endpush
@php
    $arrPayment = ['Walk-in', 'Gcash', 'Paymaya'];
@endphp
<x-landing-layout>
<section>
    <div class="relative mx-auto max-w-screen-xl px-4 py-8">
      <div class="grid grid-cols-1 items-start gap-8 md:grid-cols-2">
        <div class="sticky top-0 h-auto md:h-screen bg-base-100 z-50">
          <div class="mt-8 flex justify-between">
            <div class="max-w-lg space-y-2">
              <h1 class="text-2xl font-bold sm:text-3xl">
                Choose Your Tour Menu
              </h1>
            </div>
          </div>
  
          <div class="mt-4">
            <div class="prose w-96">
                <x-input type="number" name="pax" id="pax" placeholder="Number of Guests" />
                <x-select id="payment_method" name="payment_method" placeholder="Payment Method" :value="$arrPayment" />

            </div>
          </div>
  
          <div class="mt-8">
            <div class="mt-8 flex gap-4">
              <a href="{{route('reservation.date')}}" class="btn btn-ghost">Back</a>
              <button type="submit" class="btn btn-primary">Proceed</button>
            </div>

          </div>
        </div>
        <div class="w-full mt-8 flex flex-col justify-between">
            @foreach ($tour_category as $item)
              <h5 class="my-5 text-lg font-bold">{{$item->category}}</h5>
              <swiper-container class="mySwiper" slides-per-view="auto" pagination="true" navigation="true" space-between="30">
                  @foreach($tour_menus as $menu)
                      @if ($menu->category === $item->category)
                          <swiper-slide>
                              <div class="card w-96">
                                <input class="peer sr-only" type="checkbox" id="{{$menu->category}}{{$menu->id}}" name="tour_menu" value="{{$item->id}}">
                                <label for="{{$menu->category}}{{$menu->id}}" class="card w-full rounded-lg border border-primary shadow-lg hover:border-primary peer-checked:border-primary peer-checked:bg-primary peer-checked:text-base-100" tabindex="0">
                                    <div class="card-body">
                                        <h2 class="card-title">{{$menu->title}}</h2>
                                        <p>We are using cookies for no reason.</p>
                                    </div>
                                </label>
                              </div>
                          </swiper-slide>
                      @endif
                @endforeach
              </swiper-container>
            @endforeach
        </div>
      </div>
    </div>
  </section>
</x-landing-layout>