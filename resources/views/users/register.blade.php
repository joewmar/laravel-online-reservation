@php
    $reservation = [];
    if(request()->exists('cin', 'cout', 'at', 'px') ){
      $reservation = [
        "cin" => request()->get('cin'),
        "cout" => request()->get('cout'),
        "px" => request()->get('px'),
        "at" => request()->get('at'),
      ];
    }
@endphp
<x-landing-layout>
  <div class="flex w-full justify-center">
    <div class="card w-full md:w-[40%] bg-base-100 rounded-box my-10">
      <div class="card-body">
        <h2 class="mb-1 font-bold text-xl md:text-3xl text-center">Let's Create Account!</h2>
        <div class="flex justify-center gap-5 w-full">
          <div class="tooltip" data-tip="Sign up with Google">
            <a href={{route('google.redirect')}} class="btn btn-circle btn-outline btn-error">
              <i class="fa-brands fa-google"></i>
            </a>
          </div>
          <div href={{route('facebook.redirect')}} class="tooltip" data-tip="Sign up with Facebook">
            <a class="btn btn-circle btn-outline btn-info">
              <i class="fa-brands fa-facebook"></i>
            </a>
          </div>
        </div>
        <div class="divider">Sign up with social accounts</div>
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
          <x-datetime-picker name="birthday" id="birthday" placeholder="Birthday" class="flatpickr-bithday" />
          {{-- Nationality--}}
          <x-datalist-input id="nationality" name="nationality" placeholder="Nationality" :lists="$nationality" value="{{old('nationality') ?? ''}}" />
          {{-- Country--}}
          <x-datalist-input id="country" name="country" placeholder="Country" :lists="$countries" value="{{old('country') ?? ''}}" />
          {{-- Phone Number  --}}
          <x-phone-input />
          {{-- Email  --}}
          <x-input type="email" name="email" placeholder="Contact Email"/>

          {{-- Password --}}
          {{-- <x-input type="password" name="password" placeholder="Password"/> --}}
          <x-password />
          <x-password name="password_confirmation" id="password_confirmation" placeholder="Confirm Password" />
          {{-- Confrim Password --}}
          <div class="form-control mt-6">
              <button type="submit" class="btn btn-primary w-full">Sign up</button>
          </div>
        </form>
      </div>
      <p class="text-sm text-neutral w-full text-center">
        Already have an account?
        <a href="{{route('login')}}" class="link link-hover link-primary">Sign in </a>.
      </p>
      </div>
    </div>
  </div>


</x-landing-layout>

