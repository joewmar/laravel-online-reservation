<div id="navbar" class="navbar pt-3 transition duration-700 ease-in-out bg-transparent fixed z-10">
    <div class="navbar-start">
      <div class="dropdown">
        <label tabindex="0" class="btn btn-ghost lg:hidden text-white toggleColour">
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
              <li><a href="/profile">Profile</a></li>
              <li><a href="/reservation">My Reservation</a></li>  
              <li><a href="/reservation/step1">Book Now</a></li> 
            @endauth
            
        </ul>
      </div>
      <a class="text-white btn btn-ghost normal-case text-xl toggleColour">LOGO</a>
    </div>
    <div class="navbar-center hidden lg:flex">
      <ul class="toggleColour text-white menu menu-horizontal px-1">
        @foreach ($landingNavbar as $key => $item)
            @if ($key === $activeNav)
                <li><a class="text-primary font-semibold" href="{{$item}}">{{$key}}</a></li>
            @else
                <li><a href="{{$item}}">{{$key}}</a></li>
            @endif
        @endforeach
        @guest
          <li><a href="{{route('login')}}">Login</a></li>
        @else
          <li><a href="/reservation">My Reservation</a></li>  
          <li><a href="/reservation/step1">Book Now</a></li>  
        @endguest
      </ul>
    </div>
    <div class="navbar-end">
    @auth
      <div class="dropdown dropdown-end">
        <label tabindex="0" class="btn btn-ghost btn-circle avatar">
          <div class="w-10 rounded-full">
            <img src="{{asset('images/avatars/no-avatar.png')}}" />
          </div>
        </label>
        <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 p-2 shadow bg-base-100 rounded-box w-52">
          <li>
            <a class="justify-between">
              Profile
              <span class="badge">New</span>
            </a>
          </li>
          <li><a>Settings</a></li>
          <li>
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
              Logout
            </a>
          </li>
          
          <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
          </form>
        </ul>
      </div>
    @else
        <a class="btn bg-primary text-white" href="/reservation/step1">Book Now</a>
    @endauth
    </div>

  </div>
  
  
  
  