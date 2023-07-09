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
  <div class="flex items-center justify-center h-screen bg-base-100">
    <div class="flex justify-center rounded-box shadow-2xl w-[90%] md:w-[65%] lg:w-[65%] bg-base-100">
      <div class="card hidden md:flex rounded-l-box w-full h-auto">
        <img src="{{asset('images/main-hero3.jpg')}}" class="rounded-l-box object-cover h-full w-full"/>
      </div>
      <div class="card flex rounded-box w-full h-full p-4">
        <div class="card-body">
          <h2 class="font-bold text-3xl text-center mb-10">Let's Login!</h2>
          @if(request()->exists('cin', 'cout', 'at', 'px', 'ck'))
            <form action="{{ route('check', $reservation) }}" method="post">
          @else
            <form action="{{ route('check') }}" method="post">
          @endif
            @csrf
            <x-input type="email" name="email" placeholder="Email"/>
            <div x-data="{ show: true }" class="form-control w-full">
              <label for="password" class="relative flex rounded-md border border-gray-200 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary" >
                <input :type="show ? 'password' : 'text'" id="password" name="password" class="input input-primary peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0" placeholder="Password" />
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
              <label class="label">
              <span class="label-text-alt">
                  @error('password')
                      <span class="label-text-alt text-error">{{$message}}</span>
                  @enderror
              </span>
              </label>
          </div>
            <div class="form-control mt-6">
              <button type="submit" class="btn btn-primary">Sign in</button>
            </div>
            <div class="divider">Login with social accounts</div>
            <div class="flex justify-center gap-5 w-full my-5">
              <div class="tooltip" data-tip="Sign in with Google">
                <a class="btn btn-circle btn-outline btn-error">
                  <i class="fa-brands fa-google"></i>
                </a>
              </div>
              <div class="tooltip" data-tip="Sign in with Facebook">
                <a class="btn btn-circle btn-outline btn-info">
                  <i class="fa-brands fa-facebook"></i>
                </a>
              </div>
            </div>
            <p class="mt-4 text-sm text-neutral w-full text-center">
              Don't have an account?
              <label for="register" class="link link-hover link-primary">Create a Account</label>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</x-landing-layout>
{{-- <x-register-modal id="register" /> --}}


