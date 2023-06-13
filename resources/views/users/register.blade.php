<x-landing-layout>
  <div class="flex w-full justify-center">
    <div class="card md:w-1/2 bg-base-100 rounded-box my-10">
      <div class="card-body">
        <h2 class="mb-1 font-bold text-xl md:text-3xl text-center">Let's Create Account!</h2>
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
        <div class="divider">Sign up with social accounts</div>
        @if(session()->has('success'))
          <x-alert type="success" message="{{session('success')}}"/>
        @endif
        @if (session()->has('error'))
            <x-alert type="error" message="{{session('error')}}"/>
        @endif
        <form action="{{ route('create') }}" method="post">
          @csrf
          {{-- First Name --}}
          <x-input type="text" name="first_name" placeholder="First Name"/>
          {{-- Last Name --}}
          <x-input type="text" name="last_name" placeholder="Last Name"/>
          <x-input type="text" name="last_name" placeholder="Last Name"/>

          {{-- Birthday--}}
          {{-- <div class="form-control">
            <label class="label">
              <span class="label-text">Your Birthday</span>
            </label>
            <input type="date" class="input input-bordered input-primary" value="{{old('')}}" />
          </div> --}}
          {{-- Nationality--}}
          <x-select id="nationality" name="nationality" placeholder="Nationality" :value="$nationality" />
          {{-- Country--}}
          <x-select id="country" name="country" placeholder="Country" :value="$countries" />
          {{-- Phone Number  --}}
          <x-input type="tel" name="contact" placeholder="Phone Number"/>
          {{-- Email  --}}
          <x-input type="email" name="Email" placeholder="Contact Email"/>

          {{-- Password --}}
          <x-input type="password" name="password" placeholder="Password"/>
          {{-- Confrim Password --}}
          <x-input type="password" name="password_confirmation" placeholder="Confirm Password"/>
          <div class="form-control mt-6">
              <button type="submit" class="btn btn-primary w-full">Sign up</button>
          </div>
        </form>
      </div>
      <p class="text-sm text-neutral w-full text-center">
        Already have an account?
        <a href="/login" class="link link-hover link-primary">Sign in </a>.
      </p>
      </div>
    </div>
  </div>


</x-landing-layout>

