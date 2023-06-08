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
          @if(session()->has('success'))
          <div class="alert alert-success">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{session('message')}}</span>
          </div>
        @endif
        @if (session()->has('error'))
          <div class="alert alert-error">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>{{session('message')}}</span>
          </div>
        @endif
          <form action="{{ route('create') }}" method="post">
            @csrf
            <div class="grid grid-flow-row md:grid-cols-3 gap-2">
              {{-- First Name --}}
              <div class="form-control">
                <label class="label">
                  <span class="label-text">First Name</span>
                </label>
                <input type="text" name="first_name" class="input input-bordered input-primary" value="{{old('first_name')}}" />
                <label class="label">
                  @error('first_name')
                    <span class="label-text-alt text-error">{{$message}}</span>
                  @enderror
                </label>
              </div>
              {{-- Last Name --}}
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Last Name</span>
                </label>
                <input type="text" name="last_name" class="input input-bordered input-primary" value="{{old('last_name')}}" />
                <label class="label">
                  @error('last_name')
                    <span class="label-text-alt text-error">{{$message}}</span>
                  @enderror
                </label>
              </div>
              {{-- Birthday--}}
              {{-- <div class="form-control">
                <label class="label">
                  <span class="label-text">Your Birthday</span>
                </label>
                <input type="date" class="input input-bordered input-primary" value="{{old('')}}" />
              </div> --}}
              {{-- Nationality--}}
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Your Nationality</span>
                </label>
                <input type="text" name="nationality" class="input input-bordered input-primary" value="{{old('nationality')}}" />
                <label class="label">
                  @error('nationality')
                    <span class="label-text-alt text-error">{{$message}}</span>
                  @enderror
                </label>
              </div>
              {{-- Country--}}
              <div class="form-control">
                <label class="label">
                  <span class="label-text" >Your Country</span>
                </label>
                <input type="text" name="country" class="input input-bordered input-primary" value="{{old('country')}}" />
                <label class="label">
                  @error('country')
                    <span class="label-text-alt text-error">{{$message}}</span>
                  @enderror
                </label>
              </div>
              {{-- Phone Number  --}}
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Phone Number</span>
                </label>
                <input type="text" name="contact" class="input input-bordered input-primary" value="{{old('contact')}}" />
                <label class="label">
                  @error('contact')
                    <span class="label-text-alt text-error">{{$message}}</span>
                  @enderror
                </label>
              </div>
              {{-- Email  --}}
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Email</span>
                </label>
                <input type="email" name="email" class="input input-bordered input-primary" value="{{old('email')}}" />
                <label class="label">
                  @error('email')
                    <span class="label-text-alt text-error">{{$message}}</span>
                  @enderror
                </label>
              </div>

              {{-- Password --}}
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Password</span>
              
                </label>
                <input type="password" name="password" class="input input-bordered input-primary" value="{{old('password')}}" />
                <label class="label">
                  @error('password')
                    <span class="label-text-alt text-error">{{$message}}</span>
                  @enderror
                </label>
              </div>
              {{-- Password --}}
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Confirm Password</span>
                </label>
                <input type="password" name="password_confirmation" class="input input-bordered input-primary" value="{{old('password_confirmation')}}" />
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

