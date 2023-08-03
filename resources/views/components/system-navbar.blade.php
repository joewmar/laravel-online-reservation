<div class="fixed z-[90] top-0 left-0 w-full">
  <div class="navbar bg-base-100">
    <div class="flex-1">
      <div class="flex md:hidden btn btn-ghost btn-circle avatar">
        <x-logo />
      </div>
      <div class="hidden md:flex">
        <label @click="open = !open" class="btn btn-circle btn-ghost swap swap-rotate">
          <svg class="fill-current" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 512 512"><path d="M64,384H448V341.33H64Zm0-106.67H448V234.67H64ZM64,128v42.67H448V128Z"/></svg>
        </label>
      </div>
    </div>
    <div class="flex-none">
      {{-- <div class="dropdown dropdown-end">
        <label tabindex="0" class="btn btn-ghost btn-circle">
          <div class="indicator">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
            <span class="badge badge-sm indicator-item">8</span>
          </div>
        </label>
        <div tabindex="0" class="mt-3 card card-compact dropdown-content w-52 bg-base-100 shadow">
          <div class="card-body">
            <span class="font-bold text-lg">8 Items</span>
            <span class="text-info">Subtotal: $999</span>
            <div class="card-actions">
              <button class="btn btn-primary btn-block">View cart</button>
            </div>
          </div>
        </div>
      </div> --}}
      <div class="dropdown dropdown-end">
        <label tabindex="0" class="btn btn-ghost btn-circle avatar">
          <div class="w-10 rounded-full">
            @if(filter_var(auth('web')->user()->avatar ?? '', FILTER_VALIDATE_URL))
                <img src="{{auth('web')->user()->avatar}}" alt="" class="object-cover object-center w-full h-full rounded">
            @elseif(auth('web')->user()->avatar ?? false)
                <img src="{{asset('storage/'. auth('web')->user()->avatar)}}" alt="" class="object-cover object-center w-full h-full rounded">
            @else
                <img src="{{asset('images/avatars/no-avatar.png')}}" alt="" class="object-cover object-center w-full h-full rounded">
            @endif
          </div>
        </label>
        <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 p-2 shadow bg-base-100 rounded-box w-52">
          <li>
            <a href="/system/profile">
              Profile
            </a>
          </li>
          <li><a href="{{route('system.setting.home')}}">Settings</a></li>
          <form action="{{route('system.logout')}}" class="w-full" method="POST" id="logout-form" style="display: none;">
            @csrf
          </form>
          <li>
            {{-- <a href="">Logout</a> --}}
            <a @click="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>