@props(['type' => 'text', 'placeholder' ,'name', 'id' => '', 'placeholder' => '', 'class' =>''])
<div class="form-control w-full">
    <label class="label">
      <span class="label-text">{{$placeholder}}</span>
    </label>
    <input type="{{$type}}" placeholder="Select Date" name="{{$name}}" id="{{$id}}" class="input input-primary w-full {{$class}}" value="{{old($name)}}" />
    <label class="label">
        <span class="label-text-alt">
            @error($name)
                <span class="label-text-alt text-error">{{$message}}</span>
            @enderror
        </span>
    </label>
  </div>  