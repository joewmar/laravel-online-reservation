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
    <div class="card w-full max-w-xl bg-base-100 rounded-box my-10">
      <div class="card-body">
        <h2 class="mb-1 font-bold text-xl md:text-3xl text-center">Let's Create Account!</h2>
        <div class="flex flex-col justify-center gap-5">
          <a href={{route('google.redirect')}} class="btn btn-block btn-outline btn-error">
            <i class="fa-brands fa-google mr-4"></i>
            Sign up with Google
          </a>
          {{-- <a href={{route('facebook.redirect')}} class="btn btn-block border-blue-500 text-neutral hover:bg-blue-500 hover:text-base-100">
            <i class="fa-brands fa-facebook mr-2"></i>
          </a> --}}
        </div>
        <div class="divider">OR</div>
      @if(request()->exists('cin', 'cout', 'at', 'px', 'ck'))
        <form action="{{ route('create', $reservation) }}" method="post">
      @else
        <form action="{{ route('create') }}" method="post">
      @endif
          @csrf
          {{-- First Name --}}
          <x-input type="text" name="first_name" id="first_name" placeholder="First Name"/>
          {{-- Last Name --}}
          <x-input type="text" name="last_name" id="last_name" placeholder="Last Name"/>
          {{-- Birthday --}}
          <x-birthday-input />
          {{-- <x-birthday-input /> --}}
          {{-- Nationality--}}
          <x-datalist-input id="nationality" name="nationality" id="nationality" placeholder="Nationality" :lists="$nationality" value="{{old('nationality') ?? ''}}" />
          {{-- Country--}}
          <x-datalist-input id="country" name="country" id="country" placeholder="Country" :lists="$countries" value="{{old('country') ?? ''}}" />
          {{-- Phone Number  --}}
          <x-phone-input />
          {{-- Email  --}}
          <x-input type="email" name="email" placeholder="Contact Email"/>

          {{-- Password --}}
          {{-- <x-input type="password" name="password" placeholder="Password"/> --}}
          <x-password validation />
          <x-password name="password_confirmation" id="password_confirmation" placeholder="Confirm Password" />
          <input type="checkbox" name="acptm" class="checkbox checkbox-primary checkbox-sm" {{old('acptm') ? 'checked="checked"' : ''}}" />
          <label class="ml-1">Agree to <a href="{{route('term-conditions')}}" class="link link-primary">Terms and Conditions</a></label>
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

