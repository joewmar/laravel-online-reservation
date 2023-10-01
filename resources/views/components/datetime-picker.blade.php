@props(['type' => 'text', 'placeholder' ,'name', 'id' => '', 'placeholder' => '', 'value' => '', 'disabled' => false, 'alpineCMD' => ''])
<div class="form-control w-full {{$disabled ? 'disabledAll opacity-50' : 'opacity-100'}}">
    <label for="{{$id}}" class="w-full relative flex justify-start rounded-md border border-gray-400 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary @error($name) ring-1 ring-error border-error @enderror ">
        @if($disabled)
            <input type="{{$type}}" id="{{$id}}" name="{{$name}}" placeholder="{{$placeholder}}" {{ $attributes->merge(['class'=> "w-full cursor-pointer input input-primary peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0"])}} value="{{$value == '' ? old($name) : $value}}" {{ $attributes }} {{$alpineCMD}} disabled/>
        @else
            <input type="{{$type}}" id="{{$id}}" name="{{$name}}" placeholder="{{$placeholder}}" {{ $attributes->merge(['class'=> "w-full cursor-pointer input input-primary peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0"])}} value="{{$value == '' ? old($name) : $value}}" {{ $attributes }} {{$alpineCMD}}  />
        @endif
        <span id="{{$id}}" class="pointer-events-none absolute start-2.5 top-0 -translate-y-1/2 bg-white p-0.5 text-xs text-neutral transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-0 peer-focus:text-xs">
            {{$placeholder}}
        </span>
    </label>
    <label class="label">
        <span class="label-text-alt">
            @error($name)
                <span class="mb-5 label-text-alt text-red-500">{{$message}}</span>
            @enderror
        </span>
    </label>
  </div>