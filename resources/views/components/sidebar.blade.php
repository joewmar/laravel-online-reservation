<!-- Sidebar  -->
@props(['active' => ''])
@php
   if(auth('system')->user()->type === 0){
        $arrSideBarItems = 
        [
            "Home" => [
                "icon" => "fa-solid fa-gauge",
                "link" => route('system.home')
            ],
            "Reservation" => [
                "icon" => "fa-sharp fa-solid fa-book",
                "link" => route('system.reservation.home'),
            ],
            "Rooms" => [
                "icon" => "fa-solid fa-hotel",
                "link" => route('system.rooms.home'),
            ],
            "Tour Menu" => [
                "icon" => "fa-solid fa-route",
                "link" => route('system.menu.home'),
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
                "link" => route('system.feedback.home'),
            ],
            "Website Content" => [
                "icon" => "fa-solid fa-earth-americas",
                "link" => route('system.webcontent.home'),
            ],
        ];
   }
   elseif(auth('system')->user()->type === 1){
        $arrSideBarItems = 
            [
                "Home" => [
                    "icon" => "fa-solid fa-gauge",
                    "link" => route('system.home')
                ],
                "Reservation" => [
                    "icon" => "fa-sharp fa-solid fa-book",
                    "link" => route('system.reservation.home'),
                ],
                "Rooms" => [
                    "icon" => "fa-solid fa-hotel",
                    "link" => route('system.rooms.home'),
                ],
                "Analytics" => [
                    "icon" => "fa-solid fa-chart-simple",
                    "link" => route('system.analytics.home'),
                ],

                "Feedback" => [
                    "icon" => "fa-solid fa-comments",
                    "link" => route('system.feedback.home'),
                ],

            ];
    }
   else{
        $arrSideBarItems = 
            [
            "Reservation" => [
                "icon" => "fa-sharp fa-solid fa-book",
                "link" => route('system.reservation.home'),
            ],
            "Rooms" => [
                "icon" => "fa-solid fa-hotel",
                "link" => route('system.rooms.home'),
            ],
            "Analytics" => [
                "icon" => "fa-solid fa-chart-simple",
                "link" => route('system.analytics.home'),
            ],
        ];
   }
@endphp
<div id="sidebar" :class="!open ? 'w-56 md:w-[5rem]' : 'w-56'" class="sidebar z-[100] hidden md:block h-full overflow-hidden bg-base-100 menu" x-cloak>
    <div class="flex h-screen flex-col justify-evenly pt-2 pb-6 w-56 p-0">
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