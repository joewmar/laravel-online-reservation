<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Rooms">
        <fieldset class="w-full space-y-1 text-neutral">
            <label for="search" class="hidden">Search Room</label>
          <div class="flex justify-between">
            <div class="relative">
              <input type="search" name="search" placeholder="Search Room..." class="w-52 text-sm rounded-md sm:w-auto focus:border-primary ">
              <button class="btn btn-ghost btn-circle">
                <i class="fa-solid fa-magnifying-glass text-neutral text-xl"></i>
              </button>
            </div>
            <x-tooltip title="Setting">
              <a href="{{ route('system.setting.rooms.home') }}" class="btn btn-ghost btn-circle">
                <i class="fa-solid fa-gear text-xl"></i>
              </a>
            </x-tooltip>
          </div>
          </fieldset>
          <div class="mt-8 grid grid-flow-row md:grid-cols-3 gap-8">
            @forelse ($rooms as $room)
              @if($room->availability)
                <label for="room_modal{{$room->id}}" class="block rounded-xl border border-neutral-content p-8 shadow-md transition hover:border-error bg-error">
              @else
                <label for="room_modal{{$room->id}}" class="block rounded-xl border border-neutral-content p-8 shadow-md transition hover:border-primary">
              @endif
                <h2 class="mt-4 text-xl font-bold text-neutral">Room No. {{$room->room_no}}</h2>
                <h5 class="text-md font-medium text-neutral">{{$room->room->name}} Room ({{$room->room->min_occupancy}} to {{$room->room->max_occupancy}} Capacity)</h5>
                <h5 class="text-md font-bold text-neutral">{{$room->getAllPax() > 0 ? $room->getAllPax() . ' guest reserved' : 'No Guest'}} </h5>
              </label>
              <x-modal id="room_modal{{$room->id}}" title="Who guest on Room No. {{$room->room_no}}">
                @forelse((array)$room->customer as $key => $item)
                  <h5 class="text-md font-medium text-neutral">{{ \App\Models\Reservation::find($key)->userReservation->name()}} ({{\App\Models\Reservation::find($key)->status()}}) - {{$item}} guest</h5>
                @empty
                  <h5 class="text-md font-medium text-neutral">No guest</h5>
                @endforelse
              </x-modal>
            @empty
              <h2 class="mt-4 text-xl font-bold text-neutral col-span-full w-full text-center">No Room found</h2>
            @endforelse

          </div>
    </x-system-content>
</x-system-layout>