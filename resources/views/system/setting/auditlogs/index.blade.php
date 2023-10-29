<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Activity Log" back="{{route('system.setting.home')}}">
      <div class="mt-8 p-5">
        <div class="flex justify-end">
          <label for="srchmdl" class="btn btn-circle btn-ghost">            
            <i class="fa-solid fa-filter"></i>
          </label>
        </div>
        @include('partials.audit-filter', ['roles' => $roles])
        
          <div class="overflow-x-auto w-full">
            <table class="table w-full ">
              <!-- head -->
              <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Role</th>
                <th>Action</th>
                <th>Module</th>
                <th>Date</th>
              </tr>
              </thead>
              <tbody>
              <!-- row  -->
              @forelse ($activities as $item)
                <tr class="hover">
                  <td>{{$item->id ?? 'None'}}</td>
                  <td>{{$item->employee->name() ?? 'None'}}</td>
                  <td>{{$item->employee->role() ?? 'None'}}</td>
                  <td>{{$item->action ?? 'None'}}</td>
                  <td>{{$item->module ?? 'None'}}</td>
                  <td>{{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $item->created_at)->format('F j, Y g:ia') ?? 'None'}}</td>
                  {{-- <th>
                    <a href="{{ route('system.setting.rooms.show', encrypt($item->id)) }}" class="link link-primary">More details</a>
                  </th> --}}
                </tr>
              @empty
                  <th colspan="5" class="text-center">No Record Found</th>
              @endforelse 
              </tbody>
            </table>
          </div>
          {{$activities->links()}}
      </div>
    </x-system-content>

</x-system-layout>