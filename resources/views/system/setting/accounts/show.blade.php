<x-system-layout :activeSb="$activeSb">
    <x-system-content title="">
        <a href="{{route('system.setting.accounts')}}" class=" btn btn-ghost gap-2">
            <i class="fa-solid fa-arrow-left"></i>            
            back
          </a>
        <div class="grid grid-cols-1 gap-x-16 gap-y-8 lg:grid-cols-5 m-10">
            <div class="lg:col-span-2 lg:py-12">
              <div class="w-52">
                <img src="{{$employee->avatar != null ? asset('employee'.$employee->avatar) : asset('images/avatars/no-avatar.png') }} " alt="{{$employee->first_name}} {{$employee->last_name}} images">
            </div>
              <div class="mt-8 font-bold text-neutral text-2xl">
                {{$employee->first_name}} {{$employee->last_name}} 
              </div>
              <div class="font-normal text-neutral text-xl">
                Role: {{$employee->role()}}
              </div>
            </div>
        
            <div class="rounded-lg bg-white p-8 shadow-2xl lg:col-span-3 lg:p-12">
              <form id="employee-form" action="{{ route('system.setting.accounts.destroy', encrypt($employee->id))}}" method="POST" class="space-y-4">
                @csrf
                @method('DELETE')
                <div class="opacity-80" id="disabledAll">
                  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 my-3">
                    <x-input type="text" id="first_name" name="first_name" placeholder="First Name" value="{{$employee->first_name ?? ''}}" /> 
                    <x-input type="text" id="last_name" name="last_name" placeholder="Last Name" value="{{$employee->last_name ?? ''}}" /> 
                    {{-- <x-input type="text" id="age" name="age" placeholder="Your Age" value="{{auth('web')->user()->age() ?? ''}}" />  --}}
                    {{-- <x-select id="country" name="country" placeholder="Your Country" :value="$countries" :title="$countries" selected="{{auth('web')->user()->country ?? ''}}" /> --}}
                    {{-- <x-select id="nationality" name="nationality" placeholder="Your Nationality" :value="$nationality" :title="$nationality" selected="{{auth('web')->user()->nationality ?? ''}}" /> --}}
                    <x-input type="number" id="contact" name="contact" placeholder="Your Phone Number" value="{{$employee->contact ?? ''}}" /> 
                    <div class="col-span-2">
                      <x-input type="email" id="email" name="email" placeholder="Your Email Address" value="{{$employee->email ?? ''}}" /> 
                    </div>
                  </div>
                </div>
                  <br>
                  <div class="mt-4 flex justify-end gap-5">
                    <a href="{{route('reservation.choose')}}" class=" btn btn-primary gap-2">
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