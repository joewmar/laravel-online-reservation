<x-system-layout :activeSb="$activeSb">
    @push('styles')
      <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush
      {{-- Content  --}}
      <x-system-content title="Dashboard">
          {{-- Summary System --}}
          <div class="grid grid-col-1 md:grid-cols-2 gap-4 mt-8 md:my-8">
            <article class="flex flex-col md:flex-row items-start md:items-center justify-start md:justify-between rounded-lg border border-gray-100 bg-white p-6 shadow-md hover:border-primary hover:shadow-primary transition-all duration-300 ease-in-out space-y-3 md:space-y-0">
              <div x-data="dateData()" x-init="initDate()" class="transition-all flex items-start space-x-2">
                  <h1 class="text-xl font-mono">Date: </h1>
                  <h1 class="text-xl font-mono" x-text="currentDate"></h1>
                  <script>
                      function dateData() {
                          return {
                              currentDate: '',
                              initDate() {
                                  this.updateDate();
                              },
                              updateDate() {
                                  const now = new Date();
                                  const options = { year: 'numeric', month: 'long', day: 'numeric' };
                                  this.currentDate = now.toLocaleDateString(undefined, options);
                              }
                          };
                      }
                  </script>
              </div>
            </article>
            <article class="flex flex-col md:flex-row items-start md:items-center justify-start md:justify-between rounded-lg border border-gray-100 bg-white p-6 shadow-md hover:border-primary hover:shadow-primary transition-all duration-300 ease-in-out space-y-3 md:space-y-0">
              <div class="transition-all flex items-start space-x-2">
                <h1 class="text-xl font-mono">Time: </h1>
                <div x-data="clockData()" x-init="initClock()"  class="flex space-x-3">
                    <div>
                      <span class="countdown font-mono text-2xl">
                        <span :style="'--value:'+hours+';'"></span>
                      </span>
                      hours
                    </div> 
                    <div>
                      <span class="countdown font-mono text-2xl">
                        <span :style="'--value:'+minutes+';'"></span>
                      </span>
                      min
                    </div> 
                    <div>
                      <span class="countdown font-mono text-2xl">
                        <span :style="'--value:'+seconds+';'"></span>
                      </span>
                      sec
                    </div> 
                  </div>
                  <script>
                    function clockData() {
                        return {
                            // currentTime: '',
                            hours: '',
                            minutes: '',
                            seconds: '',
                            initClock() {
                                this.updateTime();
                                setInterval(() => this.updateTime(), 1000);
                            },
                            updateTime() {
                                const now = new Date();
                                this.hours = String(now.getHours()).padStart(2, '0');
                                this.minutes = String(now.getMinutes()).padStart(2, '0');
                                this.seconds = String(now.getSeconds()).padStart(2, '0');
                                // this.currentTime = `${hours}:${minutes}:${seconds}`;
                            }
                        };
                    }
                </script>
              </div>
            </article>
          </div>
          <div class="my-8 block md:grid grid-cols-3 space-y-4 md:gap-3">
            <x-system-card icon="fa-solid fa-earth-americas" title="Total Customer reserved online" description="{{$customers->where('type', 0)->count() ?? 0}}" />
            <x-system-card icon="fa-solid fa-users" title="Total Customer reserved physically" description="{{$customers->where('type', 1)->count() ?? 0}}" />
            <x-system-card icon="fa-solid fa-earth-americas" title="Total Customer reserved on Other Online Booking App" description="{{$customers->where('type', 2)->count() ?? 0}}" />
            <x-system-card icon="fa-solid fa-home" title="Total of Rooms" description="{{$rooms->count() > 0 ? $rooms->count() . ' Rooms' : $rooms->count() . ' Room'}}" />
            <x-system-card icon="fa-solid fa-home" title="Total Room Available" description="{{$rooms->where('availability', 0)->count() > 0 ? $rooms->where('availability', 0)->count() . ' Rooms' : $rooms->where('availability', 0)->count() . ' Room'}}" />
            <x-system-card icon="fa-solid fa-home" title="Total Room Reserved" description="{{$rooms->where('availability', 1)->count() > 0 ? $rooms->where('availability', 1)->count() . ' Rooms' : $rooms->where('availability', 1)->count() . ' Room'}}" />
            <x-system-card icon="fa-solid fa-spinner" title="Total Customer Pending" description="{{$reservations->where('status', 0)->count()}}" />
            <x-system-card icon="fa-solid fa-calendar-check" title="Total Customer Confirm" description="{{$reservations->where('status', 1)->count()}}" />
              <x-system-card icon="fa-solid fa-calendar-xmark" title="Total Customer Disapprove" description="{{$reservations->where('status', 6)->count()}}" />
            <x-system-card icon="fa-solid fa-building-circle-check" title="Total Customer Check-in" description="{{$reservations->where('status', 2)->count()}}" />
            <x-system-card icon="fa-solid fa-cash-register" title="Total Customer Check-out" description="{{$reservations->where('status', 3)->count()}}" />
            <x-system-card icon="fa-solid fa-calendar-days" title="Total Customer Reschedule" description="{{$reservations->where('status', 4)->count()}}" />
            <x-system-card icon="fa-solid fa-rectangle-xmark" title="Total Customer Cancel" description="{{$reservations->where('status', 5)->count()}}" />
              <article class="flex items-center justify-between rounded-lg border border-gray-100 bg-white p-6 shadow-md hover:border-primary hover:shadow-primary transition-all duration-300 ease-in-out">
                <div class="flex items-center gap-4">
                  <span class="hidden rounded-full bg-gray-100 p-2 text-gray-600 sm:block">
                    <i class="fa-solid fa-heart fa-bounce"></i>
                  </span>

                  <div>
                    <p class="text-sm text-gray-500">Total Feedback Rating</p>
                    <p class="text-2xl font-medium text-gray-900">
                      <div class="rating">
  
                        @for ($i = 1; $i <= 5; $i++)
                          @if($i <= $ratingAverage)
                            <input type="radio" name="rating-2" class="mask mask-star-2 bg-orange-400 cursor-default"  disabled/>
                          @else
                            <input type="radio" name="rating-2" class="mask mask-star-2 cursor-default"  disabled/>
                          @endif
                        @endfor
                      </div>
                    </p>
                    <span class="text-md font-semibold text-gray-900">{{$ratingText}}</span>
        
                  </div>
                </div>
              </article>
              <x-system-card icon="fa-solid fa-comment" title="Total Feedback Comment" description="{{$feedbacks->count() > 0 ? $feedbacks->count() . ' comments' : $feedbacks->count() .' comment'}}" />
          </div>            
          <div class="my-8 block md:grid grid-cols-2 space-y-4 md:gap-2">
            <article class="flex flex-col items-center justify-center rounded-lg border border-gray-100 bg-white py-6 shadow-md hover:border-primary hover:shadow-primary transition-all duration-300 ease-in-out">
              <h1 class="text-xl md:text-2xl font-semibold mb-5">Nationality</h1>
              <div class="w-full">
                  <canvas id="nationalityChart" width="400" height="400"></canvas>
              </div>
              <script>
                  var ctx = document.getElementById('nationalityChart').getContext('2d');
                  var nationalitiesData = {!! json_encode($nationalities) !!};
          
                  var labels = nationalitiesData.map(item => item.nationality);
                  var counts = nationalitiesData.map(item => item.count);
          
                  var chart = new Chart(ctx, {
                      type: 'pie',
                      data: {
                          labels: labels,
                          datasets: [{
                              label: 'Customer',
                              data: counts,
                              backgroundColor: ["#409122","#e9e92f","#191a3e","#ffffff","#cae2e8","#dff2a1","#f7e488","#fb7185",],
                          }],
                      },
                      options: {
                          responsive: true,
                          maintainAspectRatio: false,
                          tooltips: {
                              callbacks: {
                                  label: function(tooltipItem, data) {
                                      var dataset = data.datasets[tooltipItem.datasetIndex];
                                      var value = dataset.data[tooltipItem.index];
                                      var label = data.labels[tooltipItem.index];
                                      return label + ': ' + value;
                                  }
                              }
                          },
                      },
                  });
              </script>
            </article>
            <article class="flex items-start justify-center rounded-lg border border-gray-100 bg-white py-6 shadow-md hover:border-primary hover:shadow-primary transition-all duration-300 ease-in-out">
              <div class="flex items-center justify-center gap-4">
                <div class="text-center">
                  <h1 class="text-xl mb-5">Sales Report</h1>
                  <canvas class="w-60 md:w-96" id="todaySales"></canvas>
                </div>
              </div>
              <script>
                var ctx = document.getElementById('todaySales').getContext('2d');
                var hourlySales = @json($hourlySales);
                
                var labels = hourlySales.map(entry => entry.hour);
                var data = hourlySales.map(entry => entry.sales);
        
                var salesChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Hourly Sales',
                            data: data,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
              </script>
            </article>
          </div>    
        </div>   
      </x-system-content>
</x-system-layout>
