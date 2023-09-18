<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Rooms">
        <fieldset class="w-full space-y-1 text-neutral">
            <label for="search" class="hidden">Search Room</label>
          <div class="flex justify-between">
            <form>
              <div class="join">
                <input class="input input-bordered input-primary join-item" name="search" placeholder="Full Name"/>
                <button class="btn btn-primary join-item">Search</button>
              </div>
            </form>
            <x-tooltip title="Setting">
              <a href="{{ route('system.setting.rooms.home') }}" class="btn btn-ghost btn-circle">
                <i class="fa-solid fa-gear text-xl"></i>
              </a>
            </x-tooltip>
          </div>
          </fieldset>
          <div class="mt-8 grid grid-flow-row md:grid-cols-3 gap-8">
            @if (request('search'))
              @foreach ($rooms ?? [] as $room)
                  @foreach ((array)$room->customer as $key => $item)
                      @php $reservation = \App\Models\Reservation::find($key) ?? []; @endphp
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
                          @break
                      @else
                        <h2 class="mt-4 text-xl font-bold text-neutral col-span-full w-full text-center">No Room found</h2>
                        @break
                      @endif
                      @break
                  @endforeach
              @endforeach
            @else
              @forelse ($rooms as $room)
                  @if($room->availability)
                    <label for="room_modal{{$room->id}}" class="block rounded-xl border border-neutral-content p-8 shadow-md transition hover:border-error bg-error" disabled>
                  @else
                    <label for="room_modal{{$room->id}}" class="block rounded-xl border border-neutral-content p-8 shadow-md transition hover:border-primary">
                  @endif
                    <h2 class="mt-4 text-xl font-bold text-neutral">Room No. {{$room->room_no}}</h2>
                    <h5 class="text-md font-medium text-neutral">{{$room->room->name}} Room ({{$room->room->max_occupancy}} Capacity)</h5>
                    <h5 class="text-md font-bold text-neutral">{{$room->getAllPax() > 0 ? $room->getAllPax() . ' guest reserved' : 'No Guest'}} </h5>
                  </label>
                  <x-modal id="room_modal{{$room->id}}" title="Who guest on Room No. {{$room->room_no}}">
                    @forelse((array)$room->customer as $key => $item)
                      <h5 class="text-md font-medium text-neutral">{{ \App\Models\Reservation::find($key)->userReservation->name() ?? 'No Name'}} ({{\App\Models\Reservation::find($key)->status() ?? 'None'}}) - {{$item}} guest</h5>
                    @empty
                      <h5 class="text-md font-medium text-neutral">No guest</h5>
                    @endforelse
                  </x-modal>
                @empty
                  <h2 class="mt-4 text-xl font-bold text-neutral col-span-full w-full text-center">No Room found</h2>
                @endforelse 
            @endif

          </div>
    </x-system-content>
</x-system-layout>