@props(['id' => 'password', 'name' => 'password', 'placeholder' => 'Password', 'value' => ''])

<div x-data="{ show{{$id}}: true }" class="form-control w-full mb-4">
  <label for="{{$id}}" class="relative flex rounded-md border border-base-200 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary @error('password') ring-1 ring-error border-error @enderror" >
    <input :type="show{{$id}} ? 'password' : 'text'" id="{{$id}}" name="{{$name}}" class="input input-primary peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0" placeholder="{{$placeholder}}" value="{{$value ?? ''}}" />
    <span class="pointer-events-none absolute start-2.5 top-0 -translate-y-1/2 bg-white p-0.5 text-xs text-gray-700 transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-0 peer-focus:text-xs" >
      {{$placeholder}}
    </span>
    <span class="absolute inset-y-0 end-0 grid w-10 place-content-center">
      <button type="button" @click="show{{$id}} = !show{{$id}}" class="rounded-full bg-transparent p-0.5 text-neutral hover:text-primary" >
        <span class="sr-only">Password</span>
        <i :class="show{{$id}} ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash'"></i>
      </button>
    </span>
  </label>
  @error($name)
    <label class="label">
      <span class="label-text-alt text-error">{{$message}}</span>
    </label>
  @enderror
</div>