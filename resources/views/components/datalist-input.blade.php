@props(['type' => '', 'value' => '', 'id' => '', 'name' => '', 'placeholder' => '', 'value' => '', 'lists' => [] ])
<div class="form-control w-full">
    <label for="{{$id}}" class="relative block overflow-hidden rounded-md border border-base-200 @error($name) border-error @enderror px-3 pt-3 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary">
        <input list="{{$name}}s" id="{{$id}}" name="{{$name}}" placeholder="{{$placeholder}}" class="peer h-8 w-full border-none bg-transparent p-0 placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0 sm:text-sm" value="{{$value == '' ? old($name) : $value}}" {{ $attributes }} />
        <span id="{{$id}}" class="absolute start-3 top-3 -translate-y-1/2 text-xs text-gray-700 transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-3 peer-focus:text-xs">
            {{$placeholder}}
        </span>
    </label>
    <datalist id="{{$name}}s">
        @forelse ($lists as $item)
            <option value="{{$item}}">
        @empty
            
        @endforelse
    </datalist>
    <label class="label">
    <span class="label-text-alt">
        @error($name)
            <span class="label-text-alt text-error">{{$message}}</span>
        @enderror
    </span>
    </label>
</div>
