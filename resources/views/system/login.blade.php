<x-landing-layout>
    <div class="flex items-center justify-center h-screen bg-base-200">
      <div class="flex shadow-2xl bg-base-100">
        <div class="card flex rounded-l-box w-96 h-96">
          <img src="{{asset('images/main-hero3.jpg')}}" class="rounded-l-box object-cover h-full w-full"/>
        </div>
        <div class="card flex rounded-box w-96 h-96">
          <div class="card-body">
            <h2 class="card-title">Welcome to System!</h2>
            <div class="form-control">
              <label class="label">
                <span class="label-text">Username</span>
              </label>
              <input type="text" class="input input-bordered input-primary" autofocus/>
            </div>
            <div class="form-control">
              <label class="label">
                <span class="label-text">Password</span>
              </label>
              <input type="text" placeholder="password" class="input input-bordered input-primary" />
              <label class="label">
                <a href="#" class="label-text-alt link link-hover">Forgot password?</a>
              </label>
            </div>
            <div class="form-control mt-6">
              <button class="btn btn-primary">Login</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </x-landing-layout>
  
  