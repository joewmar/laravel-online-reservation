<x-landing-layout>
  <div class="hero min-h-screen">
    <div class="hero-content">
      <div class="card w-full bg-base-100 rounded-box">
        <div class="card-body">
          <h2 class="mb-1 font-bold text-3xl text-center">Let's Create Account!</h2>
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
          <form action="/store" method="post">
            <div class="grid grid-flow-row md:grid-cols-3 gap-2">
              {{-- First Name --}}
              <div class="form-control">
                <label class="label">
                  <span class="label-text">First Name</span>
                </label>
                <input type="text" class="input input-bordered input-primary" />
              </div>
              {{-- Last Name --}}
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Last Name</span>
                </label>
                <input type="text" class="input input-bordered input-primary" />
              </div>
              {{-- Birthday--}}
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Your Birthday</span>
                </label>
                <input type="date" class="input input-bordered input-primary" />
              </div>
              {{-- Nationality--}}
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Your Nationality</span>
                </label>
                <input type="text" class="input input-bordered input-primary" />
              </div>
              {{-- Country--}}
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Your Country</span>
                </label>
                <input type="text" class="input input-bordered input-primary" />
              </div>
              {{-- Phone Number  --}}
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Phone Number</span>
                </label>
                <input type="tel" class="input input-bordered input-primary" />
              </div>
              {{-- Email  --}}
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Email</span>
                </label>
                <input type="text" class="input input-bordered input-primary" />
              </div>

              {{-- Password --}}
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Password</span>
                </label>
                <input type="password" class="input input-bordered input-primary" />
                <label class="label">
                  <label href="#" class="label-text-alt">Error</label>
                </label>
              </div>
              {{-- Password --}}
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Confirm Password</span>
                </label>
                <input type="password" class="input input-bordered input-primary" />
              </div>
            </div>
            <div class="form-control mt-6">
              <button type="submit" class="btn btn-primary">Sign up</button>
            </div>
            <p class="mt-4 text-sm text-neutral">
              Already have an account?
              <a href="/login" class="link link-hover link-primary">Sign in </a>.
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</x-landing-layout>

