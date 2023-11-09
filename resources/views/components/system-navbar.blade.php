<div class="fixed z-50 top-0 left-0 w-full">
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
      <div class="dropdown dropdown-end">
        <label tabindex="0" class="btn btn-ghost btn-circle">
          <div class="indicator">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
            @if (count(auth('system')->user()->unreadNotifications) > 0)
              <span class="badge badge-sm indicator-item badge-primary">{{count(auth('system')->user()->unreadNotifications)}}</span>
            @endif
          </div>
        </label>
        <div tabindex="0" class="mt-3 card dropdown-content w-72 md:w-96 bg-base-100 shadow-xl">
          <div class="card-body">
            <span class="font-bold text-lg">Notifications</span>
            <div class="overflow-y-auto h-32 md:h-96">
              <ul class="menu">
                @forelse (auth('system')->user()->unreadNotifications->sortByDesc('created_at') as $notification)
                  @if(($loop->index+1) <= 8)
                    <li>
                      <a href="{{$notification->data['link'] ?? '#'}}">
                        <span>{{$notification->data['title']}}</span>
                      </a>
                    </li>
                  @endif
                @empty
                  <li>No Notifications</li>
                @endforelse 
              </ul>
            </div>
            <div class="card-actions flex justify-between">
              <a href="{{route('system.notifications')}}" class="btn btn-primary btn-block">See All</a>
            </div>
          </div>
        </div>
      </div>
      <div class="dropdown dropdown-end">
        <label tabindex="0" class="btn btn-ghost btn-circle avatar">
          <div class="w-10 rounded-full">
            @if(filter_var(auth('system')->user()->avatar ?? '', FILTER_VALIDATE_URL))
                <img src="{{auth('system')->user()->avatar}}" alt="" class="object-cover object-center w-full h-full rounded">
            @elseif(auth('system')->user()->avatar ?? false)
                <img src="{{route('private.image', ['folder' => explode('/', auth('system')->user()->avatar)[0], 'filename' => explode('/', auth('system')->user()->avatar)[1]])}}" alt="" class="object-cover object-center w-full h-full rounded">
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
          @can('admin')
            <li><a href="{{route('system.setting.home')}}">Settings</a></li>
          @endcan
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