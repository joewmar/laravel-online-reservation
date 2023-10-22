<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Accounts">
        <div class="mt-8" x-cloak>
            <div class="my-5 flex justify-between">
              <x-search endpoint="{{route('system.setting.accounts.search')}}" />
              <a href="{{route('system.setting.accounts.create')}}" class="btn btn-primary text-base-100">
                Add Account
              </a>
            </div>
            <div class="overflow-x-auto w-full shadow-2xl">
              <table class="table w-full ">
                <!-- head -->
                <thead>
                <tr>
                  <th>Full Name</th>
                  <th>Role</th>
                  <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <!-- row 1 -->
                @forelse ($employees as $employee)
                  <tr>
                    <td>
                      <div class="flex items-center space-x-3">
                        <div class="avatar">
                          <div class="mask mask-squircle w-12 h-12">
                            <img src="{{$employee->avatar ? route('private.image', ['folder' => explode('/', $employee->avatar)[0], 'filename' => explode('/', $employee->avatar)[1]]) : asset('images/avatars/no-avatar.png')}}" alt="{{$employee->name()}}" />
                          </div>
                        </div>
                        <div>
                          <div class="font-bold">{{$employee->name()}}</div>
                        </div>
                      </div>
                    </td>
                    <td>{{$employee->role()}}</td>
                    <th>
                      <a href="{{route('system.setting.accounts.show', encrypt($employee->id))}}" class="link link-primary">More details</a>
                    </th>
                  </tr>
                @empty
                  <tr>
                    <td>No Record Found</td>
                  </tr>
                @endforelse
                </tbody>
              </table>
              </div>
              {{$employees->links()}}
          </div>
    </x-system-content>
</x-system-layout>