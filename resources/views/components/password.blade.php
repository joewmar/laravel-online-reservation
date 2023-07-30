{{-- @props(['value' => '', 'id' => '', 'name' => '', 'placeholder' => '', 'value' => '', 'inputClass' => '', 'disabled' => false,  'xModel' => ''] )

<div x-data="{ show: true }" class="form-control w-full">
    <label for="password" class="relative flex rounded-md border border-base-200 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary @error($name) ring-1 ring-error border-error @enderror" >
      <input :type="show ? 'password' : 'text'" id="{{$id}}" name="{{$name}}" class="input input-primary peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0" placeholder="Password" />
      <span class="pointer-events-none absolute start-2.5 top-0 -translate-y-1/2 bg-white p-0.5 text-xs text-gray-700 transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-0 peer-focus:text-xs" >
        Password
      </span>
      <span class="absolute inset-y-0 end-0 grid w-10 place-content-center">
        <button type="button" @click="show = !show" class="rounded-full bg-transparent p-0.5 text-neutral hover:text-primary" onclick="visible()" >
          <span class="sr-only">Password</span>
          <i :class="show ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash'" x-cloak></i>
        </button>
      </span>
    </label>
    @error($name)
      <label class="label">
        <span class="label-text-alt text-error">{{$message}}</span>
      </label>
    @enderror
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
  </div> --}}