@push('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endpush

<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Reservation">
        {{-- Calendar  --}}
        <div class="flex justify-end">
            <button class="btn btn-primary gap-2">
                <i class="fa fa-user" aria-hidden="true"></i>
                Add Book
            </button>
        </div>
        <div class="my-5 p-5 w-full">
            <div id='calendar' class=""></div>
        </div> 
        {{-- Table  --}}
        <div class="mt-20 w-full">
            <div class="flex justify-between w-full">
                <div class="tabs">
                    <a href="{{route('system.reservation.home')}}" class="tab tab-bordered {{request('tab') == '' ? 'tab-active' : ''}}">All</a> 
                    <a href="{{route('system.reservation.home', 'tab=pending')}}"class="tab tab-bordered {{request('tab') == 'pending' ? 'tab-active' : ''}}">Pending</a> 
                    <a href="{{route('system.reservation.home', 'tab=confirmed')}}" class="tab tab-bordered {{request('tab') == 'confirmed' ? 'tab-active' : ''}}">Confirmed</a> 
                    <a href="{{route('system.reservation.home', 'tab=checkin')}}" class="tab tab-bordered {{request('tab') == 'checkin' ? 'tab-active' : ''}}">Check-in</a>
                    <a href="{{route('system.reservation.home', 'tab=checkout')}}" class="tab tab-bordered {{request('tab') == 'checkout' ? 'tab-active' : ''}}">Check-out</a>
                    <a href="{{route('system.reservation.home', 'tab=cancelation')}}" class="tab tab-bordered {{request('tab') == 'cancelation' ? 'tab-active' : ''}}">Cancelation</a>
                </div>
                <form action="{{route('system.reservation.home')}}">
                    <x-search />
                </form>
            </div>
            <div class="mt-10">
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
                                                    <img src="{{$list->userReservation->avatar ? asset('storage/'.$list->userReservation->avatar) : asset('images/avatars/no-avatar.png')}}" alt="{{$list->userReservation->first_name ?? ''}} {{$list->userReservation->last_name ?? ''}} Photo" />
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                    <div>
                                        <div class="font-bold">{{$list->userReservation->first_name ?? ''}} {{$list->userReservation->last_name ?? ''}}</div>
                                        <div class="text-sm opacity-50">{{$list->userReservation->country}}</div>
                                    </div>
                                    </td>
                                    <td>{{$list->age ?? ''}} years old</td>
                                    <td>{{$list->userReservation->country ?? ''}}</td>
                                    <td>{{$list->status()}}</td>
                                    <td>{{ \Carbon\Carbon::parse($list->created_at)->format('M j, Y g:i A')}}</td>

                                    <th class="w-auto">
                                        @if($list->status() == "Pending")
                                            <a href="{{route('system.reservation.show.rooms', encrypt($list->id))}}" class="btn btn-secondary btn-xs">Confirm</a>
                                            <label for="reservation" class="btn btn-success btn-xs" disabled>Check-in</label>
                                            <label for="reservation" class="btn btn-info btn-xs" disabled>Check-out</label>
                                        @elseif($list->status() == "Confirmed")
                                            @php $title = 'Check-in' @endphp
                                            <a class="btn btn-secondary btn-xs" disabled>Confirm</a>
                                            <label for="reservation" class="btn btn-success btn-xs">Check-in</label>
                                            <label for="reservation" class="btn btn-info btn-xs" disabled>Check-out</label>
                                        @elseif($list->status() == "Check-in")
                                            @php $title = 'Check-out' @endphp
                                            <a href="" class="btn btn-secondary btn-xs" disabled>Confirm</a>
                                            <label for="reservation" class="btn btn-success btn-xs" disabled>Check-in</label>
                                            <label for="reservation" class="btn btn-info btn-xs">Check-out</label>
                                        @endif
                                        <a href="{{route('system.reservation.show', encrypt($list->id))}}" class="btn btn-info btn-xs" >View</a>
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
    </x-system-content>
    @push('scripts')
        <script type="module" src='{{Vite::asset("resources/js/reservation-calendar.js")}}'></script>
    @endpush
</x-system-layout>
