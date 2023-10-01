@props(['type' => '', 'value' => '', 'id' => '', 'name' => '', 'placeholder' => '', 'lists', 'disabled' =>false])
<div class="form-control w-full {{$disabled ? 'disabledAll opacity-50' : 'opacity-100'}}">
    <label for="{{$id}}" class=" w-full relative flex justify-start rounded-md border border-gray-400 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary @error($name) ring-1 ring-error border-error @enderror ">
        <input list="{{$name}}list" id="{{$id}}" name="{{$name}}" placeholder="{{$placeholder}}" class="w-full input input-primary peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0 [&::-webkit-calendar-picker-indicator]:opacity-0" value="{{$value == '' ? old($name) : $value}}" {{ $attributes }} />
        <span id="{{$id}}" class="pointer-events-none absolute start-2.5 top-0 -translate-y-1/2 bg-white p-0.5 text-xs text-neutral transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-0 peer-focus:text-xs">
            {{$placeholder}}
        </span>
    </label>
    <datalist name="{{$id}}"  id="{{$name}}list">
        @foreach ($lists as $item)
            <option value="{{$item}}">
        @endforeach
    </datalist>
    <label class="label">
    <span class="label-text-alt">
        @error($name)
            <span class="label-text-alt text-red-500">{{$message}}</span>
        @enderror
    </span>
    </label>
</div>

