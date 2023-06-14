<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Rooms">
      
        <div class="mt-8">
          </div>
            <div class="mb-3 float-right">
              <a href="{{ route('system.setting.rooms.create') }}" class="btn btn-primary text-base-100">
                Add Room Type
              </a>
            </div>
            <div class="overflow-x-auto w-full shadow-2xl">
              <table class="table w-full ">
                <!-- head -->
                <thead>
                <tr>
                  <th>Room Name</th>
                  <th>Location</th>
                  <th>How many rooms</th>
                  <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <!-- row  -->
                @forelse ($room_lists as $item)
                  <tr>
                    <td>{{$item->name}}</td>
                    <td>{{$item->location}}</td>
                    <td>{{$item->many_room}}</td>
                    <th>
                      <a href="{{ route('system.setting.rooms.show', encrypt($item->id)) }}" class="link link-primary">More details</a>
                    </th>
                  </tr>
                @empty
                    <th colspan="4" class="text-center">No Record Found</th>
                @endforelse 
                </tbody>
                
              </table>
              </div>
          </div>
    </x-system-content>
</x-system-layout>