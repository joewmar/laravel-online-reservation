<!-- Sidebar  -->
@props(['active'])
@php
    $arrSideBarItems = 
        [
            "Home" => [
                "icon" => "fa-solid fa-gauge",
                "link" => "/system"
            ],
            "Reservation" => [
                "icon" => "fa-sharp fa-solid fa-book",
                "link" => "/system/reservation",
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
                "link" => "/system/analytics"
            ],
            "News" => [
                "icon" => "fa-solid fa-newspaper",
                "link" => "/system/news",
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
@endphp
<div id="sidebar" :class="!open ? 'w-56 md:w-[5rem]' : 'w-56'" class="sidebar z-[100] hidden md:block h-full overflow-hidden bg-base-100 menu" x-cloak>
    <div class="flex h-screen flex-col justify-center pt-2 pb-6 w-56 p-0">
        <ul class="sbList mt-6 space-y-2">
            @foreach ($arrSideBarItems as $name => $item)
                @if ($active == $name)
                    <li class="min-w-fit transition-all duration-300 ease-in-out bg-primary">
                        <a href="{{$item['link']}}" class="group flex items-center justify-start px-6 py-3">
                            <i class="h-5 w-6 text-white {{$item['icon']}}"></i>
                            <span :class="!open ? 'opacity-100 md:opacity-0' : 'opacity-100' " class="title text-white sidebar pl-2">{{$name}}</span>
                        </a>
                    </li>
                @else
                    <li class="min-w-max transition-all duration-300 ease-in-out hover:bg-primary">
                        <a href="{{$item['link']}}" class="group flex items-center justify-start px-6 py-3">
                            <i class="h-5 w-6 group-hover:text-white {{$item['icon']}}"></i>
                            <span :class="!open ? 'opacity-100 md:opacity-0' : 'opacity-100' " class="title group-hover:text-white sidebar pl-2">{{$name}}</span>
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
                <button class="active">
                    <i class="h-5 w-6 group-hover:text-white {{$item['icon']}}"></i>
                    <span class="btm-nav-label">{{$name}}</span>
                </button>
            @else
                <button>
                    <i class="h-5 w-6 group-hover:text-white {{$item['icon']}}"></i>
                    <span class="btm-nav-label">{{$name}}</span>
                </button>
            @endif
        @else
            <button @click="moreOpen = !moreOpen">
                <div :class="moreOpen && 'dropdown-open' " class="fixed bottom-16 right-10 dropdown dropdown-left dropdown-top dropdown-end">
                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                      <li><a>{{$name}}</a></li>
                    </ul>
                </div>
                <i class="fa-solid fa-ellipsis h-5 w-6 group-hover:text-white"></i>
                <span class="btm-nav-label">More</span>
            </button>   
            @break
        @endif
    @endforeach
</div>