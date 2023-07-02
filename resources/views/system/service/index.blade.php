<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Tour Menu">
        <div class="mt-8">
            <div class="mb-3 float-right">
              <a href=" {{ route('system.menu.create') }}" class="btn btn-primary text-base-100">
                Add Menu
              </a>
            </div>
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
          </div>
    </x-system-content>
</x-system-layout>