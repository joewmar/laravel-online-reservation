<x-landing-layout noFooter>
    <div class="flex items-center justify-center h-screen bg-base-100 md:bg-base-200">
      <div class="flex shadow-none md:shadow-2xl bg-base-100">
        <div class="card hidden md:flex rounded-l-box w-96 h-96">
          <img src="{{asset('images/main-hero3.jpg')}}" class="object-cover h-full w-full"/>
        </div>
        <div class="card flex rounded-box w-96 h-96">
          <div class="card-body">
            <h2 class="card-title">Welcome to System!</h2>
            <p>
              <form action=" {{route('system.check')}} " method="post">
                @csrf
              <x-input type="text" id="username" name="username" placeholder="Username" />
              <x-input type="password" id="password" name="password" placeholder="Password" />
              <div class="form-control mt-6">
                <button type="submit" class="btn btn-primary">Login</button>
              </div>
            </form>
            </p>
          </div>
        </div>
      </div>
    </div>
  </x-landing-layout>
  
  