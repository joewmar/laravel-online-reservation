
<x-landing-layout noFooter>
  <div class="flex items-center justify-center h-screen bg-transparent md:bg-base-100">
    <div class="flex justify-center rounded-box shadow-none md:shadow-2xl max-w-full md:max-w-5xl bg-transparent md:bg-base-100">
      <div class="card hidden md:flex rounded-l-box w-full h-auto">
        <img loading="lazy" src="{{asset('images/main-hero3.jpg')}}" class="rounded-l-box object-cover h-full w-full"/>
      </div>
      <div class="card flex rounded-box w-full h-full p-4">
        <div class="card-body">
          <h2 class="font-bold text-3xl text-center mb-10">Let's Login!</h2>
            <form action="{{ route('check') }}" method="post">
            @csrf
            <x-input type="email" name="email" placeholder="Email" noRequired />
            <x-password noRequired />
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
            <div class="divider">OR</div>
            <div class="flex flex-col justify-center gap-5 w-full my-5">
                <a href={{route('google.redirect')}} class="btn btn-block btn-outline btn-error">
                  <i class="fa-brands fa-google mr-4"></i>
                  Sign in with Google
                </a>
                {{-- <a href={{route('facebook.redirect')}} class="btn btn-block border-blue-500 text-neutral hover:bg-blue-500 hover:text-base-100">
                  <i class="fa-brands fa-facebook mr-2"></i>
                </a> --}}
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


