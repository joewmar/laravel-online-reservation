<!-- Sidebar  -->
@props(['active' => ''])
@php
   if(auth('system')->user()->type === 0){
        $arrSideBarItems = 
        [
            "Home" => [
                "icon" => "fa-solid fa-gauge",
                "link" => "/system"
            ],
            "Reservation" => [
                "icon" => "fa-sharp fa-solid fa-book",
                "link" => route('system.reservation.home'),
            ],
            "Rooms" => [
                "icon" => "fa-solid fa-hotel",
                "link" => "/system/rooms",
            ],
            "Tour Menu" => [
                "icon" => "fa-solid fa-route",
                "link" => "/system/menu",
            ],
            "Analytics" => [
                "icon" => "fa-solid fa-chart-simple",
                "link" => route('system.analytics.home'),
            ],
            "News" => [
                "icon" => "fa-solid fa-newspaper",
                "link" => route('system.news.home'),
            ],
            "Feedback" => [
                "icon" => "fa-solid fa-comments",
                "link" => "/system/feedback",
            ],
            "Website Content" => [
                "icon" => "fa-solid fa-earth-americas",
                "link" => "/system/webcontent",
            ],
        ];
   }
   else{
        $arrSideBarItems = 
        [
            "Home" => [
                "icon" => "fa-solid fa-gauge",
                "link" => "/system"
            ],
            "Reservation" => [
                "icon" => "fa-sharp fa-solid fa-book",
                "link" => route('system.reservation.home'),
            ],
            "Rooms" => [
                "icon" => "fa-solid fa-hotel",
                "link" => "/system/rooms",
            ],
            "Analytics" => [
                "icon" => "fa-solid fa-chart-simple",
                "link" => route('system.analytics.home'),
            ],
            "Feedback" => [
                "icon" => "fa-solid fa-comments",
                "link" => "/system/feedback",
            ],
        ];
   }
@endphp
<div id="sidebar" :class="!open ? 'w-56 md:w-[5rem]' : 'w-56'" class="sidebar z-[100] hidden md:block h-full overflow-hidden bg-base-100 menu" x-cloak>
    <div class="flex h-screen flex-col justify-center pt-2 pb-6 w-56 p-0">
        {{-- <div x-data="dateData()" x-init="initDate()"   :class="!open ? 'opacity-100 md:opacity-0' : 'opacity-100'" class="transition-all flex items-start space-x-2">
            <h1 class="text-xl font-mono">Date: </h1>
            <h1 class="text-xl font-mono mb-4" x-text="currentDate"></h1>
            <script>
                function dateData() {
                    return {
                        currentDate: '',
                        initDate() {
                            this.updateDate();
                        },
                        updateDate() {
                            const now = new Date();
                            const options = { year: 'numeric', month: 'short', day: 'numeric' };
                            this.currentDate = now.toLocaleDateString(undefined, options);
                        }
                    };
                }
            </script>
        </div>
        <div :class="!open ? 'opacity-100 md:opacity-0' : 'opacity-100'" class="transition-all flex items-start space-x-2">
            <h1 class="text-xl font-mono">Time: </h1>
            <div x-data="clockData()" x-init="initClock()"  class="flex">
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
        </div> --}}

        <ul class="sbList mt-6 space-y-2">
            @foreach ($arrSideBarItems as $name => $item)
                @if ($active == $name)
                    <li class="min-w-fit transition-all duration-300 ease-in-out bg-primary hover:bg-success">
                        <a href="{{$item['link']}}" class="group flex items-center justify-start px-6 py-3">
                            <i class="h-5 w-6 group-hover:text-success-content text-primary-content {{$item['icon']}}"></i>
                            <span :class="!open ? 'opacity-100 md:opacity-0' : 'opacity-100' " class="title text-white group-hover:text-success-content sidebar pl-2">{{$name}}</span>
                        </a>
                    </li>
                @else
                    <li class="min-w-max transition-all duration-300 ease-in-out hover:bg-primary">
                        <a href="{{$item['link']}}" class="group flex items-center justify-start px-6 py-3">
                            <i class="h-5 w-6 group-hover:text-primary-content {{$item['icon']}}"></i>
                            <span :class="!open ? 'opacity-100 md:opacity-0' : 'opacity-100' " class="title group-hover:text-primary-content sidebar pl-2">{{$name}}</span>
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
    </div>
</div>
<div x-data="{moreOpen: false}" class="btm-nav md:hidden z-[50]">
    @foreach ($arrSideBarItems as $name => $item)
        @if(($loop->index + 1) != 4)
            @if ($active == $name)
                <a href="{{$item['link']}}" class="text-primary active">
                    <i class="h-5 w-6 group-hover:text-primary-content {{$item['icon']}}"></i>
                    <span class="btm-nav-label">{{$name}}</span>
                </a>
            @else
                <a href="{{$item['link']}}">
                    <i class="h-5 w-6 group-hover:text-primary-content {{$item['icon']}}"></i>
                    <span class="btm-nav-label">{{$name}}</span>
                </a>
            @endif
        @else
            <button @click="moreOpen = !moreOpen">
                <div :class="moreOpen && 'dropdown-open' " class="fixed bottom-16 right-10 dropdown dropdown-left dropdown-top dropdown-end">
                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                        @foreach ($arrSideBarItems as $name => $item)
                            @if(($loop->index + 1) >= 4)
                                @if ($active == $name)
                                    <li class="bg-primary text-primary-content"><a href="{{$item['link']}}">{{$name}}</a></li>

                                @else
                                    <li><a href="{{$item['link']}}">{{$name}}</a></li>
                                @endif
                            @endif
                        @endforeach
                    </ul>
                </div>
                <i class="fa-solid fa-ellipsis h-5 w-6 group-hover:text-primary-content"></i>
                <span class="btm-nav-label">More</span>
            </button>   
            @break
        @endif
    @endforeach
</div>