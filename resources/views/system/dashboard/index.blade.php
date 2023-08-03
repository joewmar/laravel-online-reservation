<x-system-layout :activeSb="$activeSb">

      {{-- Content  --}}
      <x-system-content title="Dashboard">
          {{-- Summary System --}}
          <div class="my-8 block md:grid grid-cols-3 space-y-4 md:gap-3">
            <x-system-card icon="fa-solid fa-earth-americas" title="Total Customer reserved online" description="{{$countCus ?? 0}}" />
            <x-system-card icon="fa-solid fa-users" title="Total Customer physical reserved" description="25" />
            <x-system-card icon="fa-solid fa-users" title="Total Customer physical reserved" description="25" />
            <x-system-card icon="fa-solid fa-home" title="Best Room Type" description="Charlet Room" />
            <x-system-card icon="fa-solid fa-home" title="Best Room Type" description="Charlet Room" />

            <article class="flex items-center justify-between rounded-lg border border-gray-100 bg-white p-6 shadow-md hover:border-primary hover:shadow-primary transition-all duration-300 ease-in-out">
              <div class="flex items-center gap-4">
                <span class="hidden rounded-full bg-gray-100 p-2 text-gray-600 sm:block">
                  <i class="fa-solid fa-heart fa-bounce"></i>
                </span>
      
                <div>
                  <p class="text-sm text-gray-500">Total Feedback Rating</p>
      
                  <p class="text-2xl font-medium text-gray-900">
                    <div class="rating">
                      <input type="radio" name="rating-2" class="mask mask-star-2 bg-orange-400 cursor-default"  disabled/>
                      <input type="radio" name="rating-2" class="mask mask-star-2 bg-orange-400 cursor-default" disabled/>
                      <input type="radio" name="rating-2" class="mask mask-star-2 bg-orange-400 cursor-default"  disabled/>
                      <input type="radio" name="rating-2" class="mask mask-star-2 bg-orange-400 cursor-default"  disabled/>
                      <input type="radio" name="rating-2" class="mask mask-star-2 bg-orange-400 cursor-default"  disabled/>
                    </div>
                  </p>
                  <span class="text-1xl font-semibold text-gray-900">Very Satified</span>
      
                </div>
              </div>
            </article>
          </div>            
          <div class="my-8 block md:grid grid-cols-2 space-y-4 md:gap-2">
            <article class="flex items-start justify-center rounded-lg border border-gray-100 bg-white py-6 shadow-md hover:border-primary hover:shadow-primary transition-all duration-300 ease-in-out">
              <div class="flex items-center justify-center gap-4">
                <div class="text-center">
                  <h1 class="text-xl mb-5">Country Customer Chart</h1>
                  <canvas id="pie-chart"></canvas>
                </div>
              </div>
            </article>
            <article class="flex items-start justify-center rounded-lg border border-gray-100 bg-white py-6 shadow-md hover:border-primary hover:shadow-primary transition-all duration-300 ease-in-out">
              <div class="flex items-center justify-center gap-4">
                <div class="text-center">
                  <h1 class="text-xl mb-5">Sales Report</h1>
                  <canvas class="w-60 md:w-96" id="bar-chart-grouped"></canvas>
                </div>
              </div>
            </article>
          </div>    
        </div>   
      </x-system-content>
      @push('scripts')
        <script type="module" src='{{Vite::asset("resources/js/analytics-chart.js")}}'></script>
     @endpush
</x-system-layout>
