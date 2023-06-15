<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Room Setting">
      <div class="mt-8 p-5 shadow-2xl">
        <div>
          <div class="tabs">
            <a href="{{route('system.setting.rooms')}}" class="tab tab-lg tab-bordered tab-sm md:tab-lg {{$room_lists ? 'tab-active': ''}}">Guesthouse</a> 
            <a href="?tab=2" class="tab tab-lg tab-bordered tab-sm md:tab-lg {{$room_types ? 'tab-active': ''}}">Room Types</a> 
          </div>
          <div class="tab-content rounded-md">
              @if($room_lists)
              <div class="overflow-x-auto w-full">
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
                    <tr class="hover">
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
                <div class="my-5 flex justify-end">
                  <a href="{{ route('system.setting.rooms.create') }}" class="btn btn-primary text-base-100">
                    Add New Room
                  </a>
                </div>
              @endif
              {{-- Room Types --}}
              @if($room_types)
                <div class="overflow-x-auto w-full">
                  <table class="table w-full ">
                    <!-- head -->
                    <thead>
                    <tr>
                      <th>Room Type</th>
                      <th>Max Occupancy</th>
                      <th>Price</th>
                      <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- row  -->
                    @forelse ($room_types as $item)
                      <tr class="hover">
                        <td>{{$item->name}}</td>
                        <td>{{$item->max_occupancy}}</td>
                        <td>₱ {{number_format($item->price, 2)}}</td>
                        <th>
                          <div class="flex justify-around w-full">
                            <x-tooltip title="Edit">
                              <a href="{{ route('system.setting.rooms.type.edit', encrypt($item->id)) }}" class="btn btn-xs btn-ghost">
                                <i class="fa-solid fa-pencil"></i>
                              </a>
                            </x-tooltip>
                            <x-tooltip title="Delete" color="error">
                              <label for="delete_modal" class="btn btn-xs btn-ghost">                                
                                <i class="fa-solid fa-trash"></i>
                              </label>
                              <form id="delete-form" method="POST" action=" {{ route('system.setting.rooms.type.destroy', encrypt($item->id)) }}" enctype="multipart/form-data">
                                @csrf
                                @method('DELETE')
                                <x-passcode-modal title="Delete Type Confirmation" id="delete_modal" formId="delete-form" title="Do you want to remove this: {{$item->name}}"/>
                              </form>
                            </x-tooltip>
                          </div>
                        </th>
                      </tr>
                    @empty
                        <th colspan="4" class="text-center">No Record Found</th>
                    @endforelse 
                    </tbody>
                    
                  </table>
                  </div>
                  <div class="my-5 flex justify-end">
                    <a href="{{ route('system.setting.rooms.type.create') }}" class="btn btn-primary text-base-100">
                      Add Room Type
                    </a>
                  </div>
              @endif
          </div>
        </div>
      </div>
    </x-system-content>

</x-system-layout>