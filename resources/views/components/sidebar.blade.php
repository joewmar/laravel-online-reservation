<!-- Sidebar  -->
@php
    $arrSideBarItems = 
        [
            "Home" => [
                "icon" => "fa-solid fa-home",
                "link" => "/system"
            ],
            "Reservation" => [
                "icon" => "fa-sharp fa-solid fa-book",
                "link" => "/system/reservation",
            ],
            "Rooms" => [
                "icon" => "fa-solid fa-house",
                "link" => "/system/rooms",
            ],
            "Tour" => [
                "icon" => "fa-solid fa-location-dot",
                "link" => "/system/tour",
            ],
            "Analytics" => [
                "icon" => "fa-solid fa-chart-simple",
                "link" => "/system/analytics"
            ],
            "Announcement" => [
                "icon" => "fa-solid fa-newspaper",
                "link" => "/system/announcement",
            ],
            "Feedback" => [
                "icon" => "fa-solid fa-comments",
                "link" => "/system/feedback",
            ],
            "Website Content" => [
                "icon" => "fa-solid fa-browser",
                "link" => "/system/webcontent",
            ],
        ];
@endphp
<div id="sidebar" class="sidebar min-h-full w-[4rem] overflow-hidden border-r">
    <div class="flex h-full flex-col justify-between pt-2 pb-6 w-56 p-0">
        <div>
        <div class="w-max p-2.5">
            <img src="https://tailus.io/images/logo.svg" class="w-32" alt="">
        </div>
        <ul class="sbList mt-6 space-y-2">
            @foreach ($arrSideBarItems as $name => $item)
                <li class="min-w-max transition-all duration-300 ease-in-out hover:bg-primary">
                    <a href="{{$item['link']}}" class="group flex items-center justify-start px-5 py-3">
                        <i class="h-5 w-6 group-hover:text-white {{$item['icon']}}"></i>
                        <span class="title group-hover:text-white sidebar opacity-0 pl-2">{{$name}}</span>
                    </a>
                </li>
            @endforeach
        </ul>
        </div>
        <div class="w-max -mb-3">
            <a href="#" class="group flex items-center space-x-4 rounded-md px-4 py-3 text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:fill-cyan-600" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                </svg>
                <span class="group-hover:text-gray-700">Settings</span>
            </a>
        </div>
    </div>
    </div>