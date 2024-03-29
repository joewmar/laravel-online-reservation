<x-system-layout :activeSb="$activeSb">
    <x-system-content back="{{route('system.setting.accounts.home')}}">
        <div class="grid grid-cols-1 gap-x-16 gap-y-8 lg:grid-cols-5 m-10">
            <div class="lg:col-span-2 lg:py-12">
              <div class="w-52">
                <img src="{{$employee->avatar ? route('private.image', ['folder' => explode('/', $employee->avatar)[0], 'filename' => explode('/', $employee->avatar)[1]]) : asset('images/logo.png') }} " alt="{{$employee->name()}} images">
              </div>
              <div class="mt-8 font-bold text-neutral text-2xl">
                {{$employee->name()}}
              </div>
              <div class="font-normal text-neutral text-xl">
                {{$employee->role()}}
              </div>
            </div>
            <div class="rounded-lg bg-white p-8 shadow-2xl lg:col-span-3 lg:p-12 w-full">
              <form id="employee-form" action="{{ route('system.setting.accounts.destroy', encrypt($employee->id))}}" method="POST" class="space-y-4">
                @csrf
                @method('DELETE')
                <h1 class="text-2xl font-black">Details</h1>
                <div class="w-full space-y-2">
                  <h3 class="text-lg"><strong>Contact No.: </strong>{{$employee->contact ?? 'None'}}</h3>
                  <h3 class="text-lg"><strong>Email Address: </strong>{{$employee->email ?? 'None'}}</h3>
                  <h3 class="text-lg"><strong>Username: </strong>{{$employee->username ?? 'None'}}</h3>
                  <h3 class="text-lg"><strong>Telegram Username: </strong>{{$employee->telegram_username ?? 'None'}}</h3>
                  <div class="mt-5">
                    <a class="link link-primary" href="{{route('system.setting.accounts.access.control', encrypt($employee->id))}}">Access Control</a>
                  </div>
                </div>
                  <br>
                  <div class="mt-4 flex justify-end gap-5">
                    <a href="{{route('system.setting.accounts.edit', encrypt($employee->id))}}" class=" btn btn-primary gap-2">
                      Update
                    </a>
                    <label for="employee" type="submit" class="btn btn-error gap-2">
                      Delete
                    </label>
                    <x-passcode-modal title="Do you want to remove this? ({{$employee->first_name}} {{$employee->last_name}})" id="employee" formId="employee-form" />
                  </div>
    
              </form>
            </div>
          </div>
    </x-system-content>
</x-system-layout>