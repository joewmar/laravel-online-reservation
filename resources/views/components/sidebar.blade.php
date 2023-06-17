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
<div id="sidebar" class="sidebar h-full w-[5rem] hidden md:block overflow-hidden bg-base-100 menu z-50">
    <div class="flex h-screen flex-col justify-center pt-2 pb-6 w-56 p-0">
        <div class="w-full px-4 pt-10 flex items-center">
            <div class="avatar">
                <div class="w-8 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                    <img src="{{ asset('images/avatars/no-avatar.png')}}" />
                </div>
            </div>
            <span class="title text-neutral text-md uppercase font-bold sidebar opacity-0 pl-5">{{auth()->guard('system')->user()->first_name}} {{auth()->guard('system')->user()->last_name}}</span> 
        </div>
        <ul class="sbList mt-6 space-y-2">
            @foreach ($arrSideBarItems as $name => $item)
                @if ($active == $name)
                    <li class="min-w-fit transition-all duration-300 ease-in-out bg-primary">
                        <a href="{{$item['link']}}" class="group flex items-center justify-start px-6 py-3">
                            <i class="h-5 w-6 text-white {{$item['icon']}}"></i>
                            <span class="title text-white sidebar opacity-0 pl-2">{{$name}}</span>
                        </a>
                    </li>
                @else
                    <li class="min-w-max transition-all duration-300 ease-in-out hover:bg-primary">
                        <a href="{{$item['link']}}" class="group flex items-center justify-start px-6 py-3">
                            <i class="h-5 w-6 group-hover:text-white {{$item['icon']}}"></i>
                            <span class="title group-hover:text-white sidebar opacity-0 pl-2">{{$name}}</span>
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
    </div>
</div>