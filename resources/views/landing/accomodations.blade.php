<x-landing-layout>
  @push('styles')
    <style>
      swiper-container {
        width: 100%;
        height: 100%;
      }
      swiper-slide {
        font-size: 18px;
        -webkit-box-sizing: border-box;
        box-sizing: border-box;
        /* padding: 40px 60px; */
      }

      swiper-slide img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
    </style>
  @endpush
    <x-navbar :activeNav="$activeNav" />
        
      <section class="bg-base-200 text-neutral overflow-x-hidden">
        @if (isset($tours))
          <swiper-container class="mySwiper h-screen rounded" keyboard="true" space-between="30" pagination="true" pagination-clickable="true" navigation="true" rewind="true" effect="fade">
            {{-- Main Tour --}}
            <swiper-slide class="rounded-md">
              <div class="hero min-h-screen">
                <div class="hero-overlay bg-opacity-60"></div>
                <div class="absolute top-0 -z-50 h-full w-full grid grid-cols-3">
                    @foreach ($tours['mainTour'] ?? [] as $item)
                      <img src="{{asset('storage/'. $item['image'])}}" class="w-auto" alt="{{$item['title']}} Photo">
                    @endforeach
                </div>
                <div class="hero-content text-center text-neutral-content" class="w-auto h-auto">
                  <div class="max-w-md text-base-100">
                    <h1 class="mb-5 text-4xl font-bold">Tour Destination</h1>
                  </div>
                </div>
              </div>
            </swiper-slide>
            @foreach ($tours['mainTour'] ?? [] as $item)
              <swiper-slide class="rounded-md">
                <div class="hero min-h-screen" style="background-image: url({{asset('storage/'. $item['image'])}});">
                  <div class="hero-overlay bg-opacity-60"></div>
                  <div class="hero-content text-center text-neutral-content">
                    <div class="max-w-md text-base-100">
                      <h1 class="mb-5 text-4xl font-bold">{{$item['title']}}</h1>
                      <p class="mb-5">{{$item['location']}}</p>
                    </div>
                  </div>
                </div>
              </swiper-slide>
            @endforeach

            {{-- Side Tour --}}
            <swiper-slide class="rounded-md">
              <div class="hero min-h-screen">
                <div class="hero-overlay bg-opacity-60"></div>
                <div class="absolute top-0 -z-50 h-full w-full grid grid-cols-3">
                    @foreach ($tours['sideTour'] ?? [] as $item)
                      <img src="{{asset('storage/'. $item['image'])}}" class="w-auto" alt="{{$item['title']}} Photo">
                    @endforeach
                </div>
                <div class="hero-content text-center text-neutral-content" class="w-auto h-auto">
                  <div class="max-w-md text-base-100">
                    <h1 class="mb-5 text-4xl font-bold">Side Tour</h1>
                  </div>
                </div>
              </div>
            </swiper-slide>
            @foreach ($tours['sideTour'] ?? [] as $item)
              <swiper-slide class="rounded-md">
                <div class="hero min-h-screen" style="background-image: url({{asset('storage/'. $item['image'])}});">
                  <div class="hero-overlay bg-opacity-60"></div>
                  <div class="hero-content text-center text-neutral-content">
                    <div class="max-w-md text-base-100">
                      <h1 class="mb-5 text-4xl font-bold">{{$item['title']}}</h1>
                      <p class="mb-5">{{$item['location']}}</p>
                    </div>
                  </div>
                </div>
              </swiper-slide>
            @endforeach
          </swiper-container>
        @endif

          <div  class="w-full px-4 py-8 sm:py-12 sm:px-6 lg:py-16 lg:px-8">

            <div class="grid grid-cols-1 gap-y-8 lg:gap-x-16 px-4 py-8 sm:py-12 sm:px-6 lg:py-16 lg:px-8" >
              <div class="w-full flex flex-col items-center">
                <h2 class="text-3xl font-bold sm:text-4xl">Tour Services</h2>
        
                <p class="max-w-2xl mt-4 text-gray-600 text-center">
                  Explore the world with our amazing tour offers!. We've designed unforgettable travel experiences for everyone. Whether you're traveling solo, with a partner, or as a family. Our expertly guided tours offer a hassle-free way to explore new destinations, complete with comfy lodgings and knowledgeable guides.
                </p>
        
              </div>
              @foreach ($categories as $category)
                <article class="my-5">
                  <h2 class="text-2xl font-bold mb-3">{{$category->category}}</h2>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:grid-cols-3">
                    @php $menu_id = null @endphp
                    @foreach($tour_menu as $menu)
                      @if($category->category === $menu->tourMenu->category)
                        @if($menu->tourMenu->id !== $menu_id)
                          <label class="block rounded-xl border border-gray-300 p-4 shadow-md hover:border-gray-500 hover:ring-1 hover:ring-gray-400 focus:outline-none focus:ring" href="/accountant" >
                            <span class="inline-block rounded-lg bg-gray-50 p-3">
                              <i class="fa-solid fa-location-pin"></i>
                            </span>
                            <h2 class="mt-2 font-bold text-lg">{{$menu->tourMenu->title}}</h2>
                            <ul class="ml-5 sm:mt-1 sm:block sm:text-sm sm:text-gray-600">
                              @foreach (explode('(..)', $menu->tourMenu->inclusion) as $item)
                                  <li>&#10003; {{$item}}</li>
                              @endforeach
                            </ul>
                            <div class="mt-5">
                              <h3 class="mt-2 font-medium">Type</h3>
                              <ul class="ml-5 sm:mt-1 sm:block sm:text-sm sm:text-gray-600">
                                @foreach($tour_menu as $price)
                                  @if($menu->tourMenu->id === $price->tourMenu->id)
                                    <li class="list-disc">{{$price->type}}</li>
                                  @endif
                                @endforeach
                              </ul>
                            </div>
                          </label>
                        @endif
                        @php $menu_id = $menu->tourMenu->id @endphp
    
                      @endif
                    @endforeach
      
                    
                  </div>
                </article>
              @endforeach
             
            </div>
            <div class="text-neutral" >
              <div data-aos="fade-down" class="mx-auto max-w-screen-xl px-4 py-8 sm:py-12 sm:px-6 lg:py-16 lg:px-8" >
                <div class="mx-auto max-w-lg text-center">
                  <h2 class="text-3xl font-bold sm:text-4xl">Accommodation Type</h2>
                </div>
            
                <div class="mt-8 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3 place-items-center">
                  <div class="block h-72 rounded-xl border p-8 shadow-xl transition bg-base-100" data-aos="flip-right" >
                    <h2 class="mt-4 text-xl font-bold mb-2">Day Tour</h2>
                    <p class="mt-1 text-sm">
                      We offer one-day tour services that provide a fantastic opportunity to explore and experience the best attractions and activities in a single day. Our tours are designed to make the most of your limited time while ensuring an unforgettable adventure.
                    </p>
                    <ul class="mt-4 text-sm font-bold">
                      <li>We accept this offer within only 1 days.</li>
                    </ul>
                  </div>
                  <div class="block h-72 rounded-xl border p-8 shadow-xl transition bg-base-100" data-aos="flip-right" >
                    <h2 class="mt-4 text-xl font-bold mb-2">Overnight</h2>
                    <p class="mt-1 text-sm ">
                      We offer comprehensive tour services that include comfortable room stays. Our tours are designed to provide you with a memorable experience, combining guided exploration with excellent accommodation options to ensure a fantastic trip.                    
                    </p>
                    <ul class="mt-4 text-sm font-bold">
                      <li>We accept this offer within 2 to 3 days.</li>
                    </ul>
                  </div>
                  <div class="block h-72 rounded-xl border p-8 shadow-xl transition bg-base-100" data-aos="flip-right" >
                    <h2 class="mt-4 text-xl font-bold mb-2">Room Only</h2>
                    <p class="mt-1 text-sm">
                      We offer room stays without tour services, allowing our guests to enjoy a peaceful and independent experience during their stay.
                    </p>
                  </div>
            
                </div>
              </div>
            </div>
      </section>
        
    
      <section class="bg-base-100 text-gray-600 p-14 w-full">
          <div data-aos="fade-down" class="px-4 py-8 sm:py-12 sm:px-6 lg:py-16 lg:px-8">
            <div class="w-full flex flex-col items-center">
              <h2 class="text-3xl font-bold sm:text-4xl">Rooms Services</h2>
        
              <p class="max-w-2xl mt-4 text-gray-600 text-center">
                We designed for your comfort. Each room is well-equipped and maintained to ensure a great stay. Our friendly staff is here to make your experience memorable. Come and enjoy a comfortable and affordable stay with us!
              </p>
            </div>
        
            <div class="mt-20 grid grid-cols-1 md:grid-cols-3 gap-10 place-items-center">
              @foreach ($rooms as $room)
                  <div data-aos="zoom-in-up" class="card bg-base-100 shadow-xl">
                    <figure class="py-2"><img src="{{$room->image ? asset('storage/'.$room->image) : asset('images/logo.png')}}" alt="{{$room->name}}" class="object-cover h-80" /></figure>
                    <div class="card-body">
                      <h2 class="card-title">{{$room->name}}</h2>
                      <span class="text-sm">{{$room->location ?? ''}}</span>
                    </div>
                  </div>
              @endforeach
    
            </div>
        
              
          </div>
          {{-- @foreach ($rooms as $room)
            <x-modal id="{{Str::camel($room->name)}}Modal" title="{{$room->name}} Details">
              @if($room->description)
                <p>{{$room->description}}</p>
              @endif
              @if($room->amenities)
              <ul class="ml-5 sm:mt-1 sm:block sm:text-sm sm:text-gray-600">
                @foreach (explode(',', $room->amenities) as $item)
                    <li>&#10003; {{$item}}</li>
                @endforeach
              </ul>
              @endif
              @if($room->description)
                <p>Location: {{$room->location}}</p>
              @endif
          </x-modal>
        @endforeach --}}

      </section>
      <section class="bg-base-100 text-gray-600 p-14 w-full">
        <div data-aos="fade-down" class="px-4 sm:px-6 lg:px-8">
          <div class="w-full flex flex-col items-center">
            <h2 class="text-3xl font-bold sm:text-4xl">Rides Offers</h2>
      
            <p class="max-w-2xl mt-4 text-gray-600 text-center">
              Discover the excitement of our ATV and 4x4 Jeep rides! Hop on an ATV for an off-road thrill, tackling rugged terrain and scenic trails. Whether you're a thrill-seeker or nature lover, our rides offer unforgettable outdoor fun!
            </p>
          </div>
      
          <div class="mt-20 grid grid-cols-1 md:grid-cols-2 gap-10 place-items-center">
            <div data-aos="zoom-in-down" class="card w-96 bg-base-100 shadow-xl">
              <figure><img src="{{asset('images/accommodation/destinations/atv-ride.jpg')}}" alt="All Terrain Vehicle Pics" class="object-contain h-80" /></figure>
              <div class="card-body flex justify-between">
                <h2 class="card-title">All Terrain Vehicle Motors</h2>
              </div>
            </div>
            <div data-aos="zoom-in-down" class="card w-96 bg-base-100 shadow-xl">
              <figure><img src="{{asset('images/accommodation/destinations/4x4-ride.jpg')}}" alt="All Terrain Vehicle Pics" class="object-contain h-80" /></figure>
              <div class="card-body flex justify-between">
                <h2 class="card-title">4x4 Jeep Wrangler Old Model</h2>
              </div>
            </div>
  
          </div>
      
            
        </div>
      </section>
      @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-element-bundle.min.js"></script>
        <script src="{{Vite::asset("resources/js/navbar.js")}}"></script>

      @endpush
    </x-landing-layout>