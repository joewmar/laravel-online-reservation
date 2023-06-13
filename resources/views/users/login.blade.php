<x-landing-layout>
  <div class="flex items-center justify-center h-screen bg-base-100">
    <div class="flex justify-center rounded-box shadow-2xl w-[80%] bg-base-100">
      <div class="card hidden md:flex rounded-l-box w-full h-auto">
        <img src="{{asset('images/main-hero3.jpg')}}" class="rounded-l-box object-cover h-full w-full"/>
      </div>
      <div class="card flex rounded-box w-full h-auto">
        <div class="card-body">
          <h2 class="font-bold text-3xl text-center mb-10">Let's Login!</h2>
          <form action="{{ route('check') }}" method="post" autocomplete="off">
            @csrf
            <x-input type="email" name="email" placeholder="Email"/>
            <div class="form-control w-full">
              <label for="password" class="relative block overflow-hidden rounded-md border border-gray-200 px-3 pt-3 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary">
                  <input type="password" id="password" name="password" placeholder="Password" class="peer h-8 w-full border-none bg-transparent p-0 placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0 sm:text-sm" value="{{old('password')}}"/>
                  <span class="absolute start-3 top-3 -translate-y-1/2 text-xs text-gray-700 transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-3 peer-focus:text-xs">
                      Password
                  </span>
                  <span class="absolute inset-y-0 end-0 grid w-10 place-content-center">
                      <button
                        type="button"
                        class="rounded-full bg-transparent p-0.5 text-neutral hover:text-primary"
                        onclick="visible()"
                      >
                        <span class="sr-only">Password</span>
                        <i class="fa-solid fa-eye"></i>
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
              <a href="/register" for="register" class="link link-hover link-primary">Create a Account</a>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script>
      function visible() {
          let x = document.getElementById('password');
          if (x.type === "password") {
              x.type = "text";
          } else {
              x.type = "password";
          }
      }
  </script>
</x-landing-layout>
{{-- <x-register-modal id="register" /> --}}


