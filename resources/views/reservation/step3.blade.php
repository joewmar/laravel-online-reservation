@php
  $update = false;
  if(request()->has('details') && request('details') === "update"){
    $update = true;
  }

@endphp
<x-landing-layout noFooter>
  <x-full-content>
    <div x-data="{loader: false}" class="my-24 h-auto">
      <div class="flex justify-center item- pb-10 text-center ">
        <ul class="w-full steps steps-horizontal">
          <li data-content="✓" class="step step-primary">Dates</li>
          <li data-content="✓" class="step step-primary">Tour Menu</li>
          <li class="step step-primary">Your Details</li>
          <li class="step">Confirmation</li>
        </ul>
      </div>
    
      <div>
        <div class="grid grid-cols-1 gap-x-16 gap-y-8 lg:grid-cols-5 m-10">
          <div class="lg:col-span-2 lg:py-12">
            <h1 class="max-w-xl font-bold uppercase text-3xl">
              Verify the Personal information
            </h1>
            @if($update)
              <div class="mt-8 font-medium text-neutral text-lg">
                  You can change your details
              </div>
            @else
              <div class="mt-8 font-medium text-error">
                  Note: Don't worry this information will confidentials!
              </div>
            @endif
            <x-loader />
          </div>

          <div class="rounded-lg bg-white p-8 shadow-2xl lg:col-span-3 lg:p-12 {{$update ? 'border border-primary border-s-4 border-e-4' : '' }}">
            
            <div class="mt-4 flex justify-end gap-5">
                @if(!$update)
                  <a href="{{route('reservation.details', ['user' => encrypt(auth('web')->user()->id), 'details' => 'update'])}}" @click="loader = true" type="submit" class="btn btn-info btn-sm gap-2">
                    <i class="fa-solid fa-pen-to-square"></i>
                    Update
                  </a>
                @endif
            </div>
            @if($update)
              <form action="{{route('reservation.details.update', encrypt(auth('web')->user()->id))}}" method="POST">
                @csrf
                @method('PUT')
                <div>
            @else
              <form action="{{ route('reservation.details.store')}}" method="POST" class="space-y-4">
                @csrf
                <div class="opacity-80" id="disabledAll">
            @endif
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 my-3">
                  <x-input type="text" id="first_name" name="first_name" placeholder="First Name" value="{{$user->first_name ?? auth('web')->user()->first_name}}" /> 
                  <x-input type="text" id="last_name" name="last_name" placeholder="Last Name" value="{{$user->last_name ?? auth('web')->user()->last_name}}" /> 
                  @if(request()->has('details') && request('details') === "update")
                      <x-datetime-picker name="birthday" id="birthday" placeholder="Your Birthday" class="flatpickr-bithday" value="{{$user->birthday ?? old('birthday')}}" />
                  @else
                      <x-input type="text" id="age" name="age" placeholder="Your Age" value="{{auth('web')->user()->age()}}" /> 
                  @endif
                  <x-datalist-input id="country" name="country" placeholder="Your Country" :lists="$countries" value="{{$user->country ?? auth('web')->user()->country}}" />
                  <x-datalist-input id="nationality" name="nationality" placeholder="Your Nationality" :lists="$nationality" value="{{$user->nationality ?? auth('web')->user()->nationality}}" />
                  <x-input type="number" id="contact" name="contact" placeholder="Your Phone Number" value="{{$user->contact ?? auth('web')->user()->contact}}" /> 
                  <x-phone-input value="{{$user->contact ?? auth('web')->user()->contact}}" />
                  <div class="col-span-full md:col-span-2">
                    <x-input type="email" id="email" name="email" placeholder="Your Email Address" value="{{$user->email ?? auth('web')->user()->email}}" /> 
                  </div>
                </div>
              </div>
                <br>
                <div class="flex justify-end gap-5">
                  @if($update)
                    <a href="{{route('reservation.details')}}" @click="loader = true" type="submit" class="btn btn-ghost gap-2">
                      Close
                    </a>

                      <button @click="loader = true" type="submit" class="btn btn-info gap-2">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Save
                      </button>
                  @else
                    <a href="{{route('reservation.choose')}}" class="btn btn-ghost gap-2">
                      <i class="fa-solid fa-arrow-left"></i>            
                      Back
                    </a>
                    <button @click="loader = true" type="submit" class="btn btn-primary gap-2">
                      Next
                      <i class="fa-solid fa-arrow-right"></i>            
                    </button>
                  @endif
              </div>
              </form>
          </div>
        </div>
      </div>
    </div>
</x-full-content>
</x-landing-layout>
@push('scripts')
  <script type="module" src="{{Vite::asset('resources/js/validID-image.js')}}"></script>
@endphp