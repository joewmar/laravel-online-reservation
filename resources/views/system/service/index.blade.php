<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Tour Menu">
        <div class="mt-8">
            <div class="my-3 flex justify-between">
              <div class="tabs tabs-boxed bg-transparent">
                <a href="{{route('system.menu.home')}}"class="tab {{request()->has('tab') && request('tab') != 'home' ? '' : 'tab-active'}}">Menu</a> 
                <a href="{{route('system.menu.home', Arr::query(['tab' => 'addons']))}}" class="tab {{request()->has('tab') && request('tab') === 'addons' ? 'tab-active' : ''}}">Add-ons</a> 
              </div>
              @if(request()->has('tab') && request('tab') === 'addons')
                <a href=" {{ route('system.menu.addons.create') }}" class="btn btn-primary text-base-100">
                  Add Add-ons
                </a>
              @else
                <a href=" {{ route('system.menu.create') }}" class="btn btn-primary text-base-100">
                  Add Menu
                </a>
              @endif
            </div>
            @if(request()->has('tab') && request('tab') === 'addons')
                <div class="overflow-x-auto w-full shadow-2xl">
                  <table class="table w-full table-pin-cols">
                    <!-- head -->
                    <thead>
                      <tr>
                        <th>Title</th>
                        <th>Price</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                    <!-- row  -->
                    @forelse ($addons_list as $list)
                      <tr class="hover">
                        <td>{{$list->title}}</td>
                        <td>₱ {{number_format($list->price, 2)}}</td>
                        <td class="space-x-5">
                          <a href="{{ route('system.menu.addons.edit', encrypt($list->id))}}" class="link font-bold link-primary">Edit</a>
                          <label for="adddl{{$list->id}}" class="link font-bold link-error">Delete</label>      

                            <x-modal title="Do you want to remove: {{$list->title}} for ₱ {{number_format($list->price, 2)}}" id="adddl{{$list->id}}">
                                <form id="delete-form{{$list->id}}" action="{{route('system.menu.addons.destroy', encrypt($list->id))}}" method="post">
                                  @csrf 
                                  @method('DELETE')
                                <div class="modal-action">
                                  <button type="submit" class="btn btn-primary">Yes</button>
                                  <label for="adddl{{$list->id}}" class="btn btn-ghost">No</label>
                                </div>
                              </form>
                            </x-modal>
                          
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
            @else
              <div class="overflow-x-auto w-full shadow-2xl">
                  <table class="table w-full table-pin-cols">
                    <!-- head -->
                    <thead>
                      <tr>
                        <th>Menu No.</th>
                        <th>Title</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                    <!-- row  -->
                    @forelse ($tour_lists as $list)
                      <tr class="hover">
                        <td>{{$list->id}}</td>
                        <td>{{$list->title}}</td>
                        <td>
                          <a href="{{ route('system.menu.show', encrypt($list->id))}}" class="link font-bold link-primary">More details</a>
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
            @endif
          </div>
    </x-system-content>
</x-system-layout>