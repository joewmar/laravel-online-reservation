@props(['id'])
<input type="checkbox" id="{{$id}}" class="modal-toggle" />
<div class="modal modal-bottom sm:modal-middle">
  <div class="modal-box">
    <div class="modal-action">
        <label for="{{$id}}" class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</label>
    </div>
    <h3 class="font-bold text-lg">Let's Create Account!</h3>
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
        <div class="form-control mt-6">
          <button type="submit" class="btn btn-primary">Sign up</button>
        </div>
        <p class="mt-4 text-sm text-neutral w-full text-center">
          <a href="{{url()->previous()}}" class="link link-hover link-primary">Back</a>
        </p>
      </form>
  </div>
</div>