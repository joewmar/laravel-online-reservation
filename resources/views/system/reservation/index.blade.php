<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Reservation">
        {{-- Calendar  --}}
        <button class="btn btn-primary float-right gap-2">
            <i class="fa fa-user" aria-hidden="true"></i>
            Add Book
        </button>
        <div class="my-20 ">
            <div id='calendar' class=""></div>

        </div>   
    
        {{-- Table  --}}
        <div class="my-20 w-full">
            <div class="flex justify-between w-full">
                <div class="tabs">
                    <a class="tab tab-bordered tab-active">All</a> 
                    <a class="tab tab-bordered">Pending</a> 
                    <a class="tab tab-bordered ">Confirmed</a> 
                    <a class="tab tab-bordered">Cancelation</a>
                </div>
                <x-search />
            </div>
            <div class="">
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
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <!-- row 1 -->
                            @foreach ($r_list as $list)
                            <tr>
                                <td>
                                    <div class="flex items-center space-x-3">
                                        <div class="avatar">
                                            <div class="mask mask-squircle w-12 h-12">
                                                <img src="{{asset('storage/'.$list->userReservation->avatar  ?? 'images/avatars/no-avatar.png')}}" />
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
                                <th class="w-auto">
                                    @if($list->status() == "Pending")
                                        <button class="btn btn-secondary btn-xs">Confirm</button>
                                    @elseif($list->status() == "Confirmed")
                                        <button class="btn btn-success btn-xs">Check-in</button>
                                    @elseif($list->status() == "Check-in")
                                        <button class="btn btn-info btn-xs">Check-out</button>
                                    @endif
                                    <a href="" class="btn btn-circle btn-ghost">
                                        <i class="fa-solid fa-ellipsis text-lg"></i>                        
                                    </a>
                                </th>
                            </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>    
        </div>
    </x-system-content>
    @push('scripts')
        <script type="module" src='{{Vite::asset("resources/js/reservation-calendar.js")}}'></script>
    @endpush
</x-system-layout>
