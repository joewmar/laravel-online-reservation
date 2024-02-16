@push('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endpush
<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Reservation">
        {{-- Calendar  --}}
        <div  class="flex justify-between items-center mt-5">
            <div class="tabs bg-transparent tabs-boxed">
                <a href="{{route('system.reservation.home')}}" class="tab tab-md md:tab-lg {{request('rtab') === 'list' ? '' : 'tab-active'}}">Calendar</a> 
                <a href="{{route('system.reservation.home', Arr::query(['rtab' => 'list']) )}}" class="tab tab-md md:tab-lg {{request('rtab') === 'list' ? 'tab-active' : ''}}">List</a> 
            </div>
            <a href="{{route('system.reservation.create')}}" class="btn btn-sm md:btn-md btn-primary ">
                <i class="fa fa-user" aria-hidden="true"></i>
                Add Book
            </a>
        </div>
        @if(request('rtab') === 'list')
            {{-- Table  --}}
            <div class="mt-20 w-full" x-cloak>
                <div class="tabs tabs-boxed bg-transparent">
                    <a href="{{route('system.reservation.home', Arr::query(['rtab' => 'list']) )}}" class="tab {{!request()->has('tab') && request()->has('rtab') ? 'tab-active font-bold text-primary' : ''}}">All</a> 
                    @if(!(auth('system')->user()->type == 2))
                        <a href="{{route('system.reservation.home', Arr::query(['rtab' => 'list', 'tab' => 'pending']) )}}" class="tab {{request('tab') == 'pending' ? 'tab-active font-bold text-primary' : ''}}">Pending</a> 
                        <a href="{{route('system.reservation.home', Arr::query(['rtab' => 'list', 'tab' => 'confirmed']))}}" class="tab {{request('tab') == 'confirmed' ? 'tab-active font-bold text-primary' : ''}}">Confirmed</a> 
                    @endif
                    <a href="{{route('system.reservation.home', Arr::query(['rtab' => 'list', 'tab' => 'cin']))}}" class="tab {{request('tab') == 'cin' ? 'tab-active font-bold text-primary' : ''}}">Check-in</a> 
                    <a href="{{route('system.reservation.home', Arr::query(['rtab' => 'list', 'tab' => 'cout']))}}" class="tab {{request('tab') == 'cout' ? 'tab-active font-bold text-primary' : ''}}">Check-out</a> 
                    @if(!(auth('system')->user()->type == 2))
                        <a href="{{route('system.reservation.home', Arr::query(['rtab' => 'list', 'tab' => 'cancel']))}}" class="tab {{request('tab') == 'cancel' ? 'tab-active font-bold text-primary' : ''}}">Cancel</a> 
                        <a href="{{route('system.reservation.home', Arr::query(['rtab' => 'list', 'tab' => 'reschedule']))}}" class="tab {{request('tab') == 'reschedule' ? 'tab-active font-bold text-primary' : ''}}">Reschedule</a>
                        <a href="{{route('system.reservation.home', Arr::query(['rtab' => 'list', 'tab' => 'walkin']))}}" class="tab {{request('tab') == 'walkin' ? 'tab-active font-bold text-primary' : ''}}">Walk-in</a>
                        <a href="{{route('system.reservation.home', Arr::query(['rtab' => 'list', 'tab' => 'othbook']))}}" class="tab {{request('tab') == 'othbook' ? 'tab-active font-bold text-primary' : ''}}">Other Online Booking</a>
                    @endif


                    
                    <div class="overflow-x-auto w-full">
                        {{-- @if(request('tab') == 'cin' || request('tab') == 'cout')
                            <div class="max-w-xs mt-5">
                                <x-select name="wala" id="walaID" placeholder="" xModel="type" :value="['All', 'Today']" :title="['All', 'Today']" noRequired />
                            </div>
                        @endif --}}
                        <table class="table table-xs md:table-md">
                            <!-- head -->
                            <thead>
                                <tr>
                                    <th>
                                        <x-search endpoint="{{route('system.reservation.search')}}" />
                                    </th>
                                    <th>Name</th>
                                    <th>Age</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                            <!-- row 1 -->
                                @forelse ($r_list as $list)
                                    <tr>
                                        <td>
                                            <div class="flex items-center space-x-3">
                                                <div class="avatar">
                                                    <div class="mask mask-squircle w-12 h-12">
                                                        @if(isset($list->userReservation) && filter_var($list->userReservation->avatar ?? '', FILTER_VALIDATE_URL))
                                                            <img src="{{$list->userReservation->avatar}}" alt="" class="object-cover object-center w-full h-full rounded">
                                                        @elseif(isset($list->userReservation) && $list->userReservation->avatar ?? false)
                                                            <img src="{{asset('storage/'. $list->userReservation->avatar)}}" alt="" class="object-cover object-center w-full h-full rounded">
                                                        @else
                                                            <img src="{{asset('images/avatars/no-avatar.png')}}" alt="" class="object-cover object-center w-full h-full rounded">
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="font-bold">{{($list->userReservation->name() ?? $list->otherinfo['name']) ?? 'None'}}</div>
                                                <div class="text-sm opacity-50">{{($list->userReservation->country ?? $list->otherinfo['country']) ?? 'None'}}</div>
                                            </div>
                                        </td>
                                        <td>{{(($list->userReservation->age() ?? $list->otherinfo['age']) . ' years old') ?? 'None'}}</td>
                                        <td>{{$list->status()}}</td>
                                        <td>{{ \Carbon\Carbon::parse($list->created_at, 'Asia/Manila')->format('M j, Y g:i A')}}</td>
    
                                        <th class="w-auto">
                                            <a href="{{route('system.reservation.show', encrypt($list->id))}}" class="btn btn-ghost btn-sm btn-circle hover:btn-primary">
                                                <i class="fa-solid fa-circle-info"></i>
                                            </a>
                                        </th>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center font-bold">No Record Found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
    
                    </div>
                    {!! $r_list->links() !!}
                </div> 
            </div>
        @else
            @push('styles')
                <style>
                    /* Base styles for the calendar */
                    #calendar {
                        width: 100%;
                        margin: 0 auto;
                    }

                    /* Mobile-specific styles */
                    @media (max-width: 768px) {
                        #calendar {
                            font-size: 10px; /* Adjust font size for mobile */
                            /* Additional styles to optimize for mobile view */
                        }
                    }
                </style>
            @endpush
            <div class=" p-5 w-full">
                <div class="flex justify-end">
                    <div class="dropdown dropdown-end">
                        <label tabindex="0" class="btn btn-circle btn-ghost btn-xs text-blue-500">
                          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-4 h-4 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </label>
                        <ul tabindex="0" class="menu w-56 rounded-box dropdown-content z-[100] shadow bg-base-100">
                            <li>                    
                                <div class="flex items-center space-x-2">
                                    <label class="h-8 w-8 rounded-full bg-[#2a5adf] shadow-sm" ></label>
                                    <p class="font-medium">Pending</p>
                                </div>
                            </li>
                            <li>                    
                                <div class="flex items-center space-x-2">
                                    <label class="h-8 w-8 rounded-full bg-[#22c55e] shadow-sm" ></label>
                                    <p class="font-medium">Confirmed</p>
                                </div>
                            </li>
                            <li>                    
                                <div class="flex items-center space-x-2">
                                    <label class="h-8 w-8 rounded-full bg-[#eab308] shadow-sm" ></label>
                                    <p class="font-medium">Check-in</p>
                                </div>
                            </li>
                            <li>                    
                                <div class="flex items-center space-x-2">
                                    <label class="h-8 w-8 rounded-full bg-[#64748b] shadow-sm" ></label>
                                    <p class="font-medium">Check-out</p>
                                </div>
                            </li>
                            <li>                    
                                <div class="flex items-center space-x-2">
                                    <label class="h-8 w-8 rounded-full bg-[#77611e] shadow-sm" ></label>
                                    <p class="font-medium">Pending Reschedule</p>
                                </div>
                            </li>
                            <li>                    
                                <div class="flex items-center space-x-2">
                                    <label class="h-8 w-8 rounded-full bg-[#fb7185] shadow-sm" ></label>
                                    <p class="font-medium">Pending Cancel</p>
                                </div>
                            </li>
                            <li>                    
                                <div class="flex items-center space-x-2">
                                    <label class="h-8 w-8 rounded-full bg-[#f43f5e] shadow-sm" ></label>
                                    <p class="font-medium">Canceled</p>
                                </div>
                            </li>
                        </ul>
                      </div>
                </div>
                <div id='calendar' class="my-5"></div>
            </div> 
            @push('scripts')

                <script type="module" src='{{Vite::asset("resources/js/reservation-calendar.js")}}'></script>
            @endpush
        @endif
    </x-system-content>

</x-system-layout>
