<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Edit Room">
      
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
                  <th>Room Type</th>
                  <th>Occupancy</th>
                  <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <!-- row  -->
                @forelse ($accommodations as $item)
                  <tr>
                    <td>{{$item->room}}</td>
                    <td>{{$item->type}}</td>
                    <td>{{$item->occupancy}}</td>
                    <th>
                      <button href="" class="link link-primary">More details</button>
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