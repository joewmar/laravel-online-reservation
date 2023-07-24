<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Accounts">
        <div class="mt-8">
            <div class="my-5 flex justify-between">
              <form action="{{route('system.setting.accounts.search')}}" method="post">
                @csrf
                <div class="join">
                  <div>
                    <div>
                      <input type="search" name="search" class="input input-bordered join-item" placeholder="Search"/>
                    </div>
                  </div>
                  <select name="type" class="select select-bordered join-item">
                    <option value="">All</option>
                    <option value="0">Admin</option>
                    <option value="1">Manager</option>
                    <option value="2">Front Desk</option>
                    <option value="3">Staff</option>
                  </select>
                  <button class="btn btn-primary join-item">Search</button>
                </div>
              </form>
              <a href="{{route('system.setting.accounts.create')}}" class="btn btn-primary text-base-100">
                Add Account
              </a>
            </div>
            <div class="overflow-x-auto w-full shadow-2xl">
              <table class="table w-full ">
                <!-- head -->
                <thead>
                <tr>
                  <th>Id</th>
                  <th>Full Name</th>
                  <th>Role</th>
                  <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <!-- row 1 -->
                @forelse ($employees as $employee)
                  <tr>
                    <td>{{$employee->id}}</td>
                    <td>{{$employee->first_name}} {{$employee->last_name}}</td>
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