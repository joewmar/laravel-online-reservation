<x-landing-layout>
    <div class="flex items-center justify-center h-screen bg-base-200">
      <div class="flex shadow-2xl bg-base-100">
        <div class="card flex rounded-l-box w-96 h-96">
          <img src="{{asset('images/main-hero3.jpg')}}" class="rounded-l-box object-cover h-full w-full"/>
        </div>
        <div class="card flex rounded-box w-96 h-96">
          <div class="card-body">
            <h2 class="card-title">Welcome to System!</h2>
            <form action=" {{route('system.check')}} " method="post">
              @csrf
            <div class="form-control">
              <label class="label">
                <span class="label-text">Username</span>
              </label>
              <input type="text" name="username" class="input input-bordered input-primary" autofocus value="{{ old('username') }}"/>
              <label class="label">
                @error('username')
                  <span class="label-text-alt text-error">{{$message}}</span>
                @enderror
              </label>
            </div>
            <div class="form-control">
              <label class="label">
                <span class="label-text">Password</span>
              </label>
              <input type="password" name="password" class="input input-bordered input-primary" value="{{ old('password') }}"/>
              <label class="label">
                @error('password')
                  <span class="label-text-alt text-error">{{$message}}</span>
                @enderror
              </label>
            </div>
            <div class="form-control mt-6">
              <button type="submit" class="btn btn-primary">Login</button>
            </div>
          </form>
          </div>
        </div>
      </div>
    </div>
  </x-landing-layout>
  
  