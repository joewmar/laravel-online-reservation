
<x-landing-layout noFooter>
  <div class="flex items-center justify-center h-screen bg-transparent md:bg-base-100">
    <div class="flex justify-center rounded-box shadow-none md:shadow-2xl w-[90%] md:w-[65%] lg:w-[65%] bg-transparent md:bg-base-100">
      <div class="card hidden md:flex rounded-l-box w-full h-auto">
        <img src="{{asset('images/main-hero3.jpg')}}" class="rounded-l-box object-cover h-full w-full"/>
      </div>
      <div class="card flex rounded-box w-full h-full p-4">
        <div class="card-body">
          <h2 class="font-bold text-3xl text-center mb-10">Let's Login!</h2>
            <form action="{{ route('check') }}" method="post">
            @csrf
            <x-input type="email" name="email" placeholder="Email"/>
            <x-password />
            <label class="label">
              <span class="label-text-alt">
                  <span class="label-text-alt flex items-center space-x-2 cursor-pointer">
                    <input name="remember" type="checkbox" class="checkbox checkbox-primary checkbox-sm" value="1" />
                    <span >Remember Me</span>
                  </span>
              </span>
              <span class="label-text-alt">
                    <span class="label-text-alt">
                      <a href="{{route('forgot.password')}}" class="link link-primary">Forgot the password?</a>
                    </span>
              </span>
          </label>
            <div class="form-control mt-6">
              <button type="submit" class="btn btn-primary">Sign in</button>
            </div>
            <div class="divider">Login with social accounts</div>
            <div class="flex justify-center gap-5 w-full my-5">
              <div class="tooltip" data-tip="Sign in with Google">
                <a href={{route('google.redirect')}} class="btn btn-circle btn-outline btn-error">
                  <i class="fa-brands fa-google"></i>
                </a>
              </div>
              <div class="tooltip" data-tip="Sign in with Facebook">
                <a href={{route('facebook.redirect')}} class="btn btn-circle btn-outline btn-neutral">
                  <i class="fa-brands fa-facebook"></i>
                </a>
              </div>
            </div>
            <p class="mt-4 text-sm text-neutral w-full text-center">
              Don't have an account?
              <a href="{{route('register')}}" class="link link-hover link-primary">Create a Account</a>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</x-landing-layout>


