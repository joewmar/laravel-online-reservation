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
              <a href="{{ route('system.setting.rooms') }}" class="btn btn-ghost btn-circle">
                <i class="fa-solid fa-gear text-xl"></i>
              </a>
            </x-tooltip>
          </div>
          </fieldset>
          <div class="mt-8 grid grid-flow-row md:grid-cols-3 gap-8">
            @forelse ($rooms as $room)
              <a class="block rounded-xl border border-neutral-content p-8 shadow-md transition hover:border-primary">
                <h2 class="mt-4 text-xl font-bold text-neutral">Room No. {{$room->room_no}}</h2>
                <h5 class="text-md font-medium text-neutral">{{$room->room->name}} Room</h5>
                <p class="mt-1 text-sm text-neutral-600">
                  
                </p>
              </a>
            @empty
              <p class="mt-1 text-sm text-neutral-600">
                    
              </p>
            @endforelse

          </div>
    </x-system-content>
</x-system-layout>