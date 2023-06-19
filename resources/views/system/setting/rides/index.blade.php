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
                  <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <!-- row 1 -->
                  @forelse ($rides as $item)
                    <tr class="hover">
                      <td>{{$item->model}}</td>
                      <td>{{$item->max_passenger}}</td>
                      <td>{{$item->many}}</td>
                      <td>
                        <div class="flex items-center flex-wrap">
                          <a href="{{ route('system.setting.rides.edit', encrypt($item->id))}}" class="btn btn-ghost btn-sm">
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
                            <form id="delete-form" method="POST" action=" {{ route('system.setting.rides.destroy', encrypt($item->id)) }}" enctype="multipart/form-data">
                              @csrf
                              @method('DELETE')
                              <x-passcode-modal title="Delete Rate Confirmation" id="delete_modal" formId="delete-form" title="Do you want to remove this: {{$item->model}}"/>
                            </form>
                          </div>
                        </div>
                      </td>
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