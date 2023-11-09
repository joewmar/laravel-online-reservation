@props(['activeNav' => '', 'type' => ''])
<div id="navbar" class="navbar transition duration-700 ease-in-out {{$type == 'plain' ? 'bg-base-100 shadow-md' : 'bg-transparent'}} fixed z-10">
    <div class="navbar-start">
      {{-- sm screen size --}}
      <div class="dropdown">
        <label tabindex="0" class="btn btn-ghost lg:hidden {{$type == 'plain' ? 'text-neutral' : 'text-white toggleColour'}}">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" /></svg>
        </label>
        <ul tabindex="0" class="menu menu-compact dropdown-content mt-3 p-2 shadow bg-white rounded-box w-52">
            @foreach ($landingNavbar as $key => $item)
                @if ($key === $activeNav)
                  <li><a class="text-primary font-semibold" href="{{$item}}">{{$key}}</a></li>
                @else
                  <li><a href="{{$item}}">{{$key}}</a></li>
                @endif
            @endforeach
            @auth
              <li><a href="{{route('user.reservation.home')}}" class="{{$activeNav === 'My Reservation' ? 'text-primary' : ''}}">My Reservation</a></li>  
            @endauth
        </ul>
      </div>
      <a href="{{route('home')}}" class="text-white btn btn-ghost btn-circle toggleColour">
        <x-logo />
      </a>
    </div>
    {{-- md screen size --}}
    <div class="navbar-center hidden lg:flex">
      <ul class="{{$type == 'plain' ? 'text-neutral' : 'toggleColour text-white'}} menu menu-horizontal px-1">
        @foreach ($landingNavbar as $key => $item)
            @if ($key === $activeNav)
                <li><a id="actLink" class="{{$type == 'plain' ? 'text-primary' : 'text-success'}} font-semibold hover:text-primary-content hover:bg-primary" href="{{$item}}">{{$key}}</a></li>
            @else
                <li><a href="{{$item}}" class="hover:text-primary-content hover:bg-primary">{{$key}}</a></li>
            @endif
        @endforeach
        @auth
          <li><a id="actLink" href="{{route('user.reservation.home')}}" class="{{$activeNav === 'My Reservation' ? ($type == 'plain' ? 'text-primary font-semibold hover:text-primary-content hover:bg-primary' : 'text-success font-semibold hover:text-primary-content hover:bg-primary') : 'hover:text-primary-content hover:bg-primary'}}">My Reservation</a></li>  
        @endauth
      </ul>
    </div>
    <div class="navbar-end">
    @auth
    <div class="dropdown dropdown-end">
      <label tabindex="0" class="{{$type == 'plain' ? 'text-neutral' : 'toggleColour text-white'}} btn btn-ghost btn-circle">
        <div class="indicator">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
          @if(count(auth('web')->user()->unreadNotifications))
            <span class="badge badge-sm indicator-item badge-primary">{{count(auth('web')->user()->unreadNotifications)}}</span>
          @endif
        </div>
      </label>
      <div tabindex="0" class="mt-3 card dropdown-content w-72 md:w-96 bg-base-100 shadow-xl">
        <div class="card-body">
          <span class="font-bold text-lg">Notifications</span>
          <div class="overflow-y-auto h-32 md:h-96">
            <ul class="menu">
              @forelse (auth('web')->user()->unreadNotifications->sortByDesc('created_at') as $notification)
                @if(($loop->index+1) <= 8)
                  <li>
                    <a href="{{$notification->data['link'] ?? '#'}}">
                      <span>{{$notification->data['message']}}</span>
                    </a>
                  </li>
                @endif
              @empty
                <li>No Notifications</li>
              @endforelse 
            </ul>
          </div>
          <a href="{{route('user.notifications')}}" class="btn btn-primary btn-block">See All</a>
        </div>
      </div>
    </div>
      <div class="dropdown dropdown-end">
        <label tabindex="0" class="btn btn-ghost btn-circle avatar">
          <div class="w-10 rounded-full">
            @if(filter_var(auth('web')->user()->avatar ?? '', FILTER_VALIDATE_URL))
              <img src="{{asset(auth('web')->user()->avatar) ?? asset('images/avatars/no-avatar.png')}}" />
            @elseif(auth('web')->user()->avatar ?? false)
              <img src="{{auth('web')->user()->avatar ? asset('storage/'. auth('web')->user()->avatar) : asset('images/avatars/no-avatar.png')}}" />
            @else
              <img src="{{asset('images/avatars/no-avatar.png')}}" alt="" class="object-cover object-center w-full h-full rounded">
            @endif
          </div>
        </label>
        <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 p-2 shadow bg-base-100 rounded-box w-52">
          <li><span class="text-lg font-bold">{{auth('web')->user()->name()}}</span></li>
          <li>
            <a href="{{route('profile.home')}}" class="justify-between" class="{{$activeNav === 'Profile' ? 'text-primary' : ''}}">
              Profile
            </a>
          </li>
          <li>
            <a onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
              Logout
            </a>
          </li>
          <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
            @csrf
          </form>
        </ul>
      </div>
    @else
    <div class="space-x-1 flex items-center">
      <ul class="{{$type == 'plain' ? 'text-neutral' : 'toggleColour text-white'}} menu px-1 text-[10px] md:text-sm">
        <li><a href="{{route('register')}}" class="uppercase">Sign up</a></li>
      </ul>
      <a href="{{route('login')}}" class="btn btn-primary text-white">Sign in</a>
    </div>
    @endauth
    </div>
  </div>

  
  
  
  