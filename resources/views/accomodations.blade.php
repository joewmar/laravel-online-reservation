
<x-landing-layout>
 
<x-navbar :activeNav="$activeNav" type="plain" />
  <section class="pt-24 bg-base-200">
      <div class="max-w-screen-xl px-4 py-8 sm:py-12 sm:px-6 lg:py-16 lg:px-8">
        <div class="grid grid-cols-1 gap-y-8 lg:gap-x-16" >
          <div class="w-full flex flex-col items-center">
            <h2 class="text-3xl font-bold sm:text-4xl">Tour Services</h2>
    
            <p class="mt-4 text-gray-600 text-center">
              Lorem ipsum dolor sit amet consectetur adipisicing elit. Aut vero
              aliquid sint distinctio iure ipsum cupiditate? Quis, odit assumenda?
              Deleniti quasi inventore, libero reiciendis minima aliquid tempora.
              Obcaecati, autem.
            </p>
    
          </div>
          @foreach ($categories as $category)
            <article class="my-5">
              <h2 class="text-2xl font-bold mb-3">{{$category->category}}</h2>
              <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
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
                          @foreach (explode(',', $menu->tourMenu->inclusion) as $item)
                              <li>&#10003; {{$item}}</li>
                          @endforeach
                        </ul>
                        <div class="mt-5">
                          <h3 class="mt-2 font-medium">Price</h3>
                          <ul class="ml-5 sm:mt-1 sm:block sm:text-sm sm:text-gray-600">
                            @foreach($tour_menu as $price)
                              @if($menu->tourMenu->id === $price->tourMenu->id)
                                <li class="list-disc">{{$price->type}} - <span>₱</span> {{number_format($price->price, 2)}}</li>
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
      </div>
  </section>
    

  <section class="bg-white-200 text-gray-600 p-14 w-full">
      <div class="px-4 py-8 sm:py-12 sm:px-6 lg:py-16 lg:px-8">
        <div class="w-full flex flex-col items-center">
          <h2 class="text-3xl font-bold sm:text-4xl">Rooms Offer</h2>
    
          <p class="mt-4 text-gray-600 text center">
            Immerse Yourself in Comfort Make Your Guesthouse for a Memorable Stay.
          </p>
        </div>
    
        <div class="mt-20 grid grid-rows-1 md:grid-cols-3 gap-10">
          @foreach ($rooms as $room)
            <div class="card bg-base-100 shadow-xl">
              <figure><img src="{{$room->image ? asset('storage/'.$room->image) : asset('images/logo.png')}}" alt="{{$room->name}}" /></figure>
              <div class="card-body">
                <h2 class="card-title">{{$room->name}}</h2>
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
                <div class="card-actions justify-end">
                  {{-- <button class="btn btn-primary">Book Now</button> --}}
                </div>
              </div>
            </div>
          @endforeach

        </div>
    
          
      </div>
  </section>
</x-landing-layout>