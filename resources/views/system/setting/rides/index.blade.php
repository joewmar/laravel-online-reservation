<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Ride Vehicles">
     
        <div class="mt-8">
            <div class="mb-3 float-right">
              <a href="{{ route('system.setting.rides.create')}}" class="btn btn-primary text-base-100">
                Add Ride
              </a>
            </div>
            <div class="overflow-x-auto w-full shadow-2xl">
              <table class="table w-full ">
                <!-- head -->
                <thead>
                <tr>
                  <th>Ride Vechile Model</th>
                  <th>Maximium of Passenger</th>
                  <th>How many vechicle</th>
                  <th colspan="2">Action</th>
                </tr>
                </thead>
                <tbody>
                <!-- row 1 -->
                  @forelse ($rides as $item)
                    <tr class="hover">
                      <td>{{$item->model}}</td>
                      <td>{{$item->max_passenger}}</td>
                      <td>{{$item->many}}</td>
                      <th>
                        <x-tooltip title="Edit {{$item->model}}">
                          <a href="{{ route('system.setting.rides.edit', encrypt($item->id))}}" class="btn btn-ghost btn-sm">
                            <i class="fa-solid fa-pencil"></i>
                          </a>
                        </x-tooltip>
                        <x-tooltip title="Delete {{$item->model}}">
                          <label for="delete_modal" class="btn btn-xs btn-ghost">                                
                            <i class="fa-solid fa-trash"></i>
                          </label>
                          <form id="delete-form" method="POST" action=" {{ route('system.setting.rides.destroy', encrypt($item->id)) }}" enctype="multipart/form-data">
                            @csrf
                            @method('DELETE')
                            <x-passcode-modal title="Delete Rate Confirmation" id="delete_modal" formId="delete-form" title="Do you want to remove this: {{$item->model}}"/>
                          </form>
                      </x-tooltip>

                      </th>
                    </tr>
                  @empty
                  <tr>
                    <td colspan="4" class="text-center">No Record Found</td>
                  </tr>
                  @endforelse
                
                </tbody>
                
              </table>
              </div>
          </div>
    </x-system-content>
</x-system-layout>