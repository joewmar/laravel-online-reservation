@push('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endpush
<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Reservation">
        {{-- Calendar  --}}
        <div class="flex justify-between items-center mt-5">
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
            <div class="mt-20 w-full">
                <div class="tabs md:tabs-boxed md:bg-transparent flex justify-center md:justify-start">
                    <a href="{{route('system.reservation.home', Arr::query(['rtab' => 'list']) )}}" class="tab {{!request()->has('tab') && request()->has('rtab') ? 'tab-active font-bold text-primary' : ''}}">All</a> 
                    <a href="{{route('system.reservation.home', Arr::query(['rtab' => 'list', 'tab' => 'pending']) )}}" class="tab {{request('tab') == 'pending' ? 'tab-active font-bold text-primary' : ''}}">Pending</a> 
                    <a href="{{route('system.reservation.home', Arr::query(['rtab' => 'list', 'tab' => 'confirmed']))}}" class="tab {{request('tab') == 'confirmed' ? 'tab-active font-bold text-primary' : ''}}">Confirmed</a> 
                    <a href="{{route('system.reservation.home', Arr::query(['rtab' => 'list', 'tab' => 'cin']))}}" class="tab {{request('tab') == 'cin' ? 'tab-active font-bold text-primary' : ''}}">Check-in</a> 
                    <a href="{{route('system.reservation.home', Arr::query(['rtab' => 'list', 'tab' => 'cout']))}}" class="tab {{request('tab') == 'cout' ? 'tab-active font-bold text-primary' : ''}}">Check-out</a> 
                    <a href="{{route('system.reservation.home', Arr::query(['rtab' => 'list', 'tab' => 'cancel']))}}" class="tab {{request('tab') == 'cancel' ? 'tab-active font-bold text-primary' : ''}}">Cancel</a> 
                    <a href="{{route('system.reservation.home', Arr::query(['rtab' => 'list', 'tab' => 'reshedule']))}}" class="tab {{request('tab') == 'reshedule' ? 'tab-active font-bold text-primary' : ''}}">Reschedule</a>
                    <a href="{{route('system.reservation.home', Arr::query(['rtab' => 'list', 'tab' => 'previous']))}}" class="tab {{request('tab') == 'previous' ? 'tab-active font-bold text-primary' : ''}}">Previous</a>
                </div>
                <div class="mt-10">
                    <div class="flex justify-end w-full gap-5 -z-[100]">
                        {{-- <form action="{{route('system.reservation.search', Arr::query(['rtab' => 'list', 'tab' => 'list']))}}" method="POST">
                            @csrf
                            <div class="join">
                                <div>
                                <div>
                                    <input class="input input-sm input-primary join-item placeholder:text-sm" name="search" placeholder="Search Full Name..." value="{{request('search') ?? ""}}" />
                                </div>
                                </div>
                                <button class="btn btn-sm join-item btn-primary">Search</button>
                            </div>
                        </form> --}}
                    </div>
                    <div class="overflow-x-auto w-full">
                        <table class="table w-full">
                            <!-- head -->
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Name</th>
                                    <th>Age</th>
                                    <th>Nationality</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Action</th>
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
                                                        <img src="{{$list->userReservation->avatar !== null ? asset('storage/'.$list->userReservation->avatar) : asset('images/avatars/no-avatar.png')}}" alt="{{$list->userReservation->name() ?? ''}} Photo" />
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                        <div>
                                            <div class="font-bold">{{$list->userReservation->name() ?? ''}}</div>
                                            <div class="text-sm opacity-50">{{$list->userReservation->country}}</div>
                                        </div>
                                        </td>
                                        <td>{{$list->age ?? ''}} years old</td>
                                        <td>{{$list->userReservation->country ?? ''}}</td>
                                        <td>{{$list->status()}}</td>
                                        <td>{{ \Carbon\Carbon::parse($list->created_at)->format('M j, Y g:i A')}}</td>
    
                                        <th class="w-auto">
                                            <div class="join">
                                                @if($list->status() == "Pending")
                                                    <a href="{{route('system.reservation.show.rooms', encrypt($list->id))}}" class="btn btn-secondary btn-xs join-item">Confirm</a>
                                                    <label class="btn btn-success btn-xs join-item" disabled>Check-in</label>
                                                    <label class="btn btn-info btn-xs join-item" disabled>Check-out</label>
                                                @elseif($list->status() == "Confirmed")
                                                    <a class="btn btn-secondary btn-xs join-item" disabled>Confirm</a>
                                                    <label for="checkin" class="btn btn-success btn-xs join-item">Check-in</label>
                                                    <x-checkin name="{{$list->userReservation->name() ?? ''}}" :datas="$list" />
                                                    <label class="btn btn-info btn-xs join-item" disabled>Check-out</label>
                                                @elseif($list->status() == "Check-in")
                                                    <a href="" class="btn btn-secondary btn-xs join-item" disabled>Confirm</a>
                                                    <label class="btn btn-success btn-xs join-item" disabled>Check-in</label>
                                                    <label for="checkout" class="btn btn-warning btn-xs join-item">Check-out</label>
                                                    <x-checkout name="{{$list->userReservation->name() ?? ''}}" :datas="$list" />
                                                @endif
                                                <a href="{{route('system.reservation.show', encrypt($list->id))}}" class="btn btn-info btn-xs join-item">View</a>
                                                <div class="dropdown dropdown-left dropdown-end">
                                                    <label tabindex="0" class="btn join-item btn-xs">                                                        
                                                        <i class="fa-solid fa-ellipsis"></i>
                                                    </label>
                                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
                                                        <li><a href="{{route('system.reservation.show.cancel', encrypt($list->id))}}" class="text-error">Cancel</a></li>
                                                        <li><a href="{{route('system.reservation.show.reschedule', encrypt($list->id))}}" class="text-accent-content">Reschedule</a></li>
                                                    </ul>
                                                  </div>
                                            </div>
                                            {{-- <a href="{{route('system.reservation.show.receipt', encrypt($list->id))}}" class="btn btn-accent btn-xs" >Receipt</a> --}}
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
            <div class="my-5 p-5 w-full">
                <div id='calendar' class=""></div>
            </div> 
            @push('scripts')
                <script type="module" src='{{Vite::asset("resources/js/reservation-calendar.js")}}'></script>
            @endpush
        @endif
    </x-system-content>

</x-system-layout>
