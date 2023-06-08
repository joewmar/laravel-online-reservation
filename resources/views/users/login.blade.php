<x-landing-layout>
  <div class="flex items-center justify-center h-screen bg-base-100">
    <div class="flex justify-center rounded-box shadow-2xl w-[80%] bg-base-100">
      <div class="card hidden md:flex rounded-l-box w-full h-auto">
        <img src="{{asset('images/main-hero3.jpg')}}" class="rounded-l-box object-cover h-full w-full"/>
      </div>
      <div class="card flex rounded-box w-full h-auto">
        <div class="card-body">
          <h2 class="mb-5 font-bold text-3xl text-center">Let's Login!</h2>
          <div class="flex justify-center gap-5 w-full">
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
          <div class="divider">OR</div>
          <form action="{{ route('check') }}" method="post" autocomplete="off">
            @csrf
            <div class="form-control mb-5">
              <label class="label">
                <span class="label-text">Email</span>
              </label>
              <input type="text" name="email" class="input input-bordered input-primary" />
              <label class="label">
                @error('email')
                  <span class="label-text-alt text-error">{{$message}}</span>
                @enderror
              </label>
            </div>
            <div class="form-control">
              <label class="label">
                <span class="label-text">Password</span>
              </label>
              <input type="password" name="password" class="input input-bordered input-primary" />
              <label class="label">
                @error('password')
                  <span class="label-text-alt text-error">{{$message}}</span>
                @enderror
              </label>
              {{-- <label class="label">
                <label href="#" class="label-text-alt">
                  <input type="checkbox" checked="checked" class="checkbox checkbox-primary mr-2" />
                  Remember me
                </label>
                <a href="#" class="label-text-alt link link-hover link-primary">Forgot password?</a>
              </label> --}}
            </div>
            <div class="form-control mt-6">
              <button type="submit" class="btn btn-primary">Sign in</button>
            </div>
            <p class="mt-4 text-sm text-neutral">
              Don't have an account?
              <a href="/register" for="register" class="link link-hover link-primary">Create a Account</a>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</x-landing-layout>
{{-- <x-register-modal id="register" /> --}}


