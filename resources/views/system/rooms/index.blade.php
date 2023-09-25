<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Rooms">
        <fieldset class="w-full space-y-1 text-neutral">
          <div x-data="{type: '{{request('date') ? 'date' : 'name'}}'}" class="flex justify-between items-center mt-5 w-full">
            <form action="{{route('system.rooms.home')}}" method="POST">
              @csrf
              <div>
                <span class="text-lg">Search by: </span>
                <input x-model="type" id="name" class="my-2 radio radio-primary" type="radio" value="name" />
                <label :aria-checked="type == 'name'" :class="type == 'name' ? 'mr-5 text-primary' : 'mr-5'" for="name" class="my-5">Name</label>  
                <input x-model="type" id="date" class="my-2 radio radio-primary" type="radio" value="date" />
                <label :aria-checked="search == 'date'" :class="type == 'date' ? 'mr-5 text-primary' : 'mr-5'" for="date" class="my-5">Date</label>
              </div>
                <div x-show="type == 'name'">
                  <div class="join mt-3">
                    <input type="search" class="input input-bordered input-primary btn-sm md:btn-md join-item" :name="type == 'name' ? 'name' : '' " placeholder="Full Name"/>
                    <button class="btn btn-primary btn-sm md:btn-md join-item">Search</button>
                  </div>
                </div>
                <div x-show="type == 'date'">
                  <div class="join mt-3">
                      <input type="text" class="input input-bordered input-primary btn-sm md:btn-md join-item flatpickr-reservation-one" :name="type == 'date' ? 'date' : '' " placeholder="Date" value="{{request('date') ?? Carbon\Carbon::now('Asia/Manila')->format('Y-m-d')}}" />
                      <button class="btn btn-primary btn-sm md:btn-md join-item">Search</button>
                  </div>
                </div>
            </form>
            <div>
              <x-tooltip title="Setting">
                <a href="{{ route('system.setting.rooms.home') }}" class="btn btn-ghost btn-circle">
                  <i class="fa-solid fa-gear text-xl"></i>
                </a>
              </x-tooltip>
            </div>
          </div>
          </fieldset>
          <h2 class="{{request('search') ? 'hidden' : 'block'}}  mt-8 text-xl font-bold">Date: {{Carbon\Carbon::createFromFormat('Y-m-d', request('date') ?? Carbon\Carbon::now('Asia/Manila')->format('Y-m-d'))->format('F j, Y')}}</h2>
          <h2 class="{{request('search') ? 'block' : 'hidden'}} mt-8 text-xl font-bold">Name: {{request('name') ?? 'None'}}</h2>
          <div class="mt-3 grid grid-flow-row md:grid-cols-3 gap-8">
            @if (request('search'))
              @foreach ($rooms ?? [] as $room)
                @php $isFound = false; @endphp

                  @foreach ((array)$room->customer as $key => $item)
                      @php 
                        $reservation = \App\Models\Reservation::find($key) ?? []; 
                      @endphp
                      @if ($reservation->userReservation->name() === request('search'))
                          <label for="room_modal{{$room->id}}" class="block rounded-xl border border-neutral-content p-8 shadow-md transition hover:border-primary">
                            <h2 class="mt-4 text-xl font-bold text-neutral">Room No. {{$room->room_no}}</h2>
                            <h5 class="text-md font-medium text-neutral">{{$room->room->name}}</h5>
                            <h5 class="text-md font-bold text-neutral">{{$reservation->userReservation->name() ?? 'No Name'}} ({{$reservation->pax ?? 'No'}} Guest)</h5>
                          </label>
                          <x-modal id="room_modal{{$room->id}}" title="Room No. {{$room->room_no}}: {{$reservation->userReservation->name()}} ({{$reservation->status()}})">
                                <table class="table">
                                  <tbody>
                                    <tr>
                                      <th>Age</th>
                                      <td>{{$reservation->age}} years old</td>
                                    </tr>
                                    <tr>
                                      <th>Guest</th>
                                      <td>{{$reservation->pax}} guest</td>
                                    </tr>
                                    <tr>
                                      <th>Check-in</th>
                                      <td>{{ \Carbon\Carbon::createFromFormat('Y-m-d', $reservation->check_in )->format('l, F j, Y') ?? 'None'}}</td>
                                    </tr>
                                    <tr>
                                      <th>Check-out</th>
                                      <td>{{ \Carbon\Carbon::createFromFormat('Y-m-d', $reservation->check_out )->format('l, F j, Y') ?? 'None'}}</td>
                                    </tr>
                                    <tr>
                                      <th>Check-out</th>
                                      <td>{{ \Carbon\Carbon::createFromFormat('Y-m-d', $reservation->check_out )->format('l, F j, Y') ?? 'None'}}</td>
                                    </tr>
                                  </tbody>
                                </table>
                          </x-modal>
                          @php $isFound = true; @endphp
                          @break
                      @else
                        @php $isFound = false; @endphp
                        @break
                      @endif
                  @endforeach
                  @if (!$isFound)
                  <h2 class="mt-4 text-xl font-bold text-neutral col-span-full w-full text-center">No Room found</h2>

                  @break
                @endif
              @endforeach
            @else
              @forelse ($rooms as $room)
                  <label for="room_modal{{$room->id}}" class="block rounded-xl border border-neutral-content p-8 shadow-md transition  {{in_array($room->id, $reserved) ? 'bg-red-500 text-white hover:border-neutral' :'text-neutral hover:border-primary'}}">
                    <h2 class="mt-4 text-xl font-bold">Room No. {{$room->room_no}}</h2>
                    <h5 class="text-md font-medium">{{$room->room->name}} Room ({{$room->room->max_occupancy}} Capacity)</h5>
                    <h5 class="text-md font-bold">{{$room->getAllPax() > 0 ? $room->getAllPax() . ' guest reserved' : 'No Guest'}} </h5>
                  </label>
                  <x-modal id="room_modal{{$room->id}}" title="Who guest on Room No. {{$room->room_no}}">
                    @forelse((array)$room->customer as $key => $item)
                      <h5 class="text-md font-medium">{{ \App\Models\Reservation::find($key)->userReservation->name() ?? 'No Name'}} ({{\App\Models\Reservation::find($key)->status() ?? 'None'}}) - {{$item}} guest</h5>
                    @empty
                      <h5 class="text-md font-medium">No guest</h5>
                    @endforelse
                  </x-modal>
                @empty
                  <h2 class="mt-4 text-xl font-bold col-span-full w-full text-center">No Room found</h2>
                @endforelse 
            @endif

          </div>
    </x-system-content>
  
</x-system-layout>