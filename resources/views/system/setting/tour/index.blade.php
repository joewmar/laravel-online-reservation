<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Tour Destination">
        <div class="mt-8">
            <div class="mb-3 float-right">
              <a href=" {{ route('system.setting.tour.create') }}" class="btn btn-primary text-base-100">
                Add Tour Destination
              </a>
            </div>
            <div class="overflow-x-auto w-full shadow-2xl">
              <table class="table w-full table-pin-cols">
                <!-- head -->
                <thead>
                  <tr>
                    <th>Tour Name</th>
                    <th>Description</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                <!-- row  -->
                @forelse ($tours as $tour)
                  <tr class="hover">
                    <td>
                      <div class="flex items-center space-x-3">
                        <div class="avatar">
                          <div class="mask mask-squircle w-12 h-12">
                            <img src="{{$tour->image ? asset('storage/'. $tour->image) : asset('images/avatars/no-avatar.png')}}" alt="{{$tour->name}} Img" />
                          </div>
                        </div>
                        <div>
                          <div class="font-bold">{{$tour->name}}</div>
                          <div class="text-sm opacity-50">{{$tour->location}}</div>
                        </div>
                      </div>
                      
                    </td>
                    <td>{{$tour->description}}</td>
                    <td>
                      <div class="flex items-center flex-wrap">
                        <a href="{{ route('system.setting.tour.edit', encrypt($tour->id))}}" class="btn btn-ghost btn-sm">
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
                          <form id="delete-form" method="POST" action=" {{ route('system.setting.tour.destroy', encrypt($tour->id)) }}" enctype="multipart/form-data">
                            @csrf
                            @method('DELETE')
                            <x-passcode-modal title="Delete Rate Confirmation" id="delete_modal" formId="delete-form" title="Do you want to remove this: {{$tour->name}}"/>
                          </form>
                        </div>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr class="text-center">
                    <th colspan="4" >No Record Found</th>
                  </tr>             
                @endforelse
                </tbody>
                
              </table>
              </div>
          </div>
    </x-system-content>
</x-system-layout>