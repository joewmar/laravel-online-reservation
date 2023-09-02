@props(['id'])
<input type="checkbox" id="{{$id}}" class="modal-toggle" />
<div class="modal modal-bottom sm:modal-middle">
  <div class="modal-box">
    <div class="modal-action">
        <label for="{{$id}}" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</label>
    </div>
    <h3 class="font-bold text-lg">Let's Create Account!</h3>
    <div class="flex justify-center gap-5 w-full">
        <div class="tooltip" data-tip="Sign up with Google">
          <a class="btn btn-circle btn-outline btn-error">
            <i class="fa-brands fa-google"></i>
          </a>
        </div>
        <div class="tooltip" data-tip="Sign up with Facebook">
          <a class="btn btn-circle btn-outline btn-info">
            <i class="fa-brands fa-facebook"></i>
          </a>
        </div>
      </div>
      <div class="divider">OR</div>
      @if(request()->exists('cin', 'cout', 'at', 'px', 'ck'))
        <form action="{{ route('create', $reservation) }}" method="post">
      @else
        <form action="{{ route('create') }}" method="post">
      @endif
        @csrf
        {{-- First Name --}}
        <x-input type="text" name="first_name" placeholder="First Name"/>
        {{-- Last Name --}}
        <x-input type="text" name="last_name" placeholder="Last Name"/>
        {{-- Birthday --}}
        <x-birthday-input />
        {{-- Nationality--}}
        <x-datalist-input id="nationality" name="nationality" placeholder="Nationality" :lists="$nationality" value="{{old('nationality') ?? ''}}" />
        {{-- Country--}}
        <x-datalist-input id="country" name="country" placeholder="Country" :lists="$countries" value="{{old('country') ?? ''}}" />
        {{-- Phone Number  --}}
        <x-phone-input />
        {{-- Email  --}}
        <x-input type="email" name="email" placeholder="Contact Email"/>

        <div x-data="{ show: true }" class="form-control w-full mb-4">
          <label for="password" class="relative flex rounded-md border border-base-200 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary @error('password') ring-1 ring-error border-error @enderror" >
            <input :type="show ? 'password' : 'text'" name="password" class="input input-primary peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0" placeholder="Password" />
            <span class="pointer-events-none absolute start-2.5 top-0 -translate-y-1/2 bg-white p-0.5 text-xs text-gray-700 transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-0 peer-focus:text-xs" >
              Password
            </span>
            <span class="absolute inset-y-0 end-0 grid w-10 place-content-center">
              <button type="button" @click="show = !show" class="rounded-full bg-transparent p-0.5 text-neutral hover:text-primary" onclick="visible()" >
                <span class="sr-only">Password</span>
                <i :class="show ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash'" x-cloak></i>
              </button>
            </span>
          </label>
          @error('password')
            <label class="label">
              <span class="label-text-alt text-error">{{$message}}</span>
            </label>
          @enderror
        </div>
        <div x-data="{ showConfirm: true }" class="form-control w-full mb-4">
          <label for="password_confirmation" class="relative flex rounded-md border border-base-200 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary @error('password') ring-1 ring-error border-error @enderror" >
            <input :type="showConfirm ? 'password' : 'text'" id="password_confirmation" name="password_confirmation" class="input input-primary peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0" placeholder="Confirm Password" />
            <span class="pointer-events-none absolute start-2.5 top-0 -translate-y-1/2 bg-white p-0.5 text-xs text-gray-700 transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-0 peer-focus:text-xs" >
              Confirm Password
            </span>
            <span class="absolute inset-y-0 end-0 grid w-10 place-content-center">
              <button type="button" @click="showConfirm = !showConfirm" class="rounded-full bg-transparent p-0.5 text-neutral hover:text-primary" >
                <span class="sr-only">Password</span>
                <i :class="showConfirm ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash'" x-cloak></i>
              </button>
            </span>
          </label>
          @error('password_confirmation')
            <label class="label">
              <span class="label-text-alt text-error">{{$message}}</span>
            </label>
          @enderror
        </div>
        <div class="form-control mt-6">
            <button type="submit" class="btn btn-primary w-full">Sign up</button>
        </div>
      </form>
  </div>
</div>