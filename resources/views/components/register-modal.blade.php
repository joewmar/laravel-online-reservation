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
        <x-datetime-picker name="birthday" id="birthday" placeholder="Birthday" class="flatpickr-bithday" />
        {{-- Nationality--}}
        <x-select id="nationality" name="nationality" placeholder="Nationality" :value="$nationality" :title="$nationality" selected="{{old('nationality') ?? ''}}" />
        {{-- Country--}}
        <x-select id="country" name="country" placeholder="Country" :value="$countries" :title="$countries" selected="{{old('country') ?? ''}}" />
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
</div>