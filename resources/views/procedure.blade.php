<x-full-content>
        <div class="mx-auto max-w-screen-xl px-5 py-8 sm:py-12 sm:px-6 lg:py-16 lg:px-8" >
          <div class="mx-auto max-w-lg text-center">
            <h2 class="text-3xl font-bold sm:text-4xl capitalize">How to make reservation?</h2>
          </div>

          <div class="px-14 mt-20 grid grid-cols-1 gap-8 md:grid-cols-7 place-items-center">
            <div class="flex flex-col items-center justify-start h-full gap-4">
                <div class="flex justify-center items-center text-2xl text-primary-content bg-primary rounded-full w-16 h-16">
                    <i class="fa-solid fa-calendar-days "></i>
                </div>
                <div class="text-lg font-bold uppercase">Step 1</div>
                <div class="w-full md:w-60 text-sm text-center capitalize">Select your preferred date, the number of guests, and your preferred type of accommodation to make your reservation.</div>
            </div>
            <div class="flex flex-col items-center justify-start h-full">
                <div class="flex justify-center items-center text-2xl w-16 h-16">
                    <i class="hidden md:inline fa-solid fa-arrow-right"></i>                
                    <i class="inline md:hidden fa-solid fa-arrow-down"></i>                
                </div>
            </div>
            <div class="flex flex-col items-center justify-start h-full gap-4">
                <div class="flex justify-center items-center text-2xl text-primary-content bg-primary rounded-full w-16 h-16">
                    <i class="fa-solid fa-map-location-dot"></i>
                </div>
                <div class="text-lg font-bold uppercase">Step 2</div>
                <div class="w-full md:w-60 text-sm text-center capitalize">Choose the type of tour service, select your preferred payment method, and specify the number of guests who will be joining the tour.</div>
            </div>
            <div class="flex flex-col items-center justify-start h-full">
                <div class="flex justify-center items-center text-2xl w-16 h-16">
                    <i class="hidden md:inline fa-solid fa-arrow-right"></i>                
                    <i class="inline md:hidden fa-solid fa-arrow-down"></i>                
                </div>
            </div>
            <div class="flex flex-col items-center justify-start h-full gap-4">
                <div class="flex justify-center items-center text-2xl text-primary-content bg-primary rounded-full w-16 h-16">
                    <i class="fa-solid fa-id-card"></i>
                </div>
                <div class="text-lg font-bold uppercase">Step 3</div>
                <div class="w-full md:w-60 text-sm text-center capitalize">Verify or update your basic personal information</div>
            </div>
            <div class="flex flex-col items-center justify-start h-full">
                <div class="flex justify-center items-center text-2xl w-16 h-16">
                    <i class="hidden md:inline fa-solid fa-arrow-right"></i>                
                    <i class="inline md:hidden fa-solid fa-arrow-down"></i>                
                </div>
            </div>
            <div class="flex flex-col items-center justify-start h-full gap-4">
                <div class="flex justify-center items-center text-2xl text-primary-content bg-primary rounded-full w-16 h-16">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="text-lg font-bold uppercase">Step 4</div>
                <div class="w-full md:w-60 text-sm text-center capitalize">Verify the overall reservation information you have inputted and then wait for confirmation. </div>
            </div>
          </div>
          <div class="mt-12 text-center">
            <a href="{{route('reservation.demo')}}" class="btn btn-primary" >
              See More
            </a>
          </div>
        </div>
  
</x-full-content>