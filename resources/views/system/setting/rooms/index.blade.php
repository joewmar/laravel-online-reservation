<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Room Setting">
      <div class="mt-8 p-5 shadow-2xl">
        <div>
          <div class="tabs">
            <a href="{{route('system.setting.rooms.home')}}" class="tab tab-lg tab-bordered tab-sm md:tab-lg {{$room_lists ? 'tab-active': ''}}">Type</a> 
            <a href="?tab=2" class="tab tab-lg tab-bordered tab-sm md:tab-lg {{$room_rates ? 'tab-active': ''}}">Rate</a> 
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
              {{-- Room Rate --}}
              @if($room_rates)
                <div class="overflow-x-auto w-full">
                  <table class="table w-full ">
                    <!-- head -->
                    <thead>
                    <tr>
                      <th>Room Rate</th>
                      <th>Number of Guests</th>
                      <th>Price</th>
                      <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- row  -->
                    @forelse ($room_rates as $item)
                      <tr class="hover">
                        <td>{{$item->name}}</td>
                        <td>{{$item->occupancy}}</td>
                        <td>₱ {{number_format($item->price, 2)}}</td>
                        <td>
                          <div class="flex items-center flex-wrap">
                            <a href="{{ route('system.setting.rooms.rate.edit', encrypt($item->id))}}" class="btn btn-ghost btn-sm">
                              <div class="text-primary">
                                <i class="fa-solid fa-pencil"></i>
                                Edit
                              </div>
                            </a>
                            <div>
                              <label for="delete_modal" class="btn btn-sm btn-ghost">                                
                                <div class="text-error">
                                  <i class="fa-solid fa-trash"></i>
                                  Remove
                                </div>
                              </label>
                              <form id="delete-form" method="POST" action=" {{ route('system.setting.rooms.rate.destroy', encrypt($item->id)) }}" enctype="multipart/form-data">
                                @csrf
                                @method('DELETE')
                                <x-passcode-modal title="Delete Rate Confirmation" id="delete_modal" formId="delete-form" title="Do you want to remove this: {{$item->name}}"/>
                              </form>
                            </div>
                          </div>
                        </td>
                      </tr>
                    @empty
                        <th colspan="4" class="text-center">No Record Found</th>
                    @endforelse 
                    </tbody>
                    
                  </table>
                  </div>
                  <div class="my-5 flex justify-end">
                    <a href="{{ route('system.setting.rooms.rate.create') }}" class="btn btn-primary text-base-100">
                      Add New Rate
                    </a>
                  </div>
              @endif
          </div>
        </div>
      </div>
    </x-system-content>

</x-system-layout>