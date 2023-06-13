@props(['id' => '', 'name' => '', 'value', 'placeholder'])
<div class="form-control w-full">
    <label for="{{$id}}" class="relative block overflow-hidden rounded-md border border-gray-200 px-3 pt-3 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary">
        <select name="{{$name}}" id="{{$id}}" class="peer h-8 w-full border-none bg-transparent p-0 placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0 sm:text-sm" value="{{old($name)}}">
            <option value="" disabled selected>Please select</option>
            @foreach ($value as $key => $item)
                <option value="{{$item}}">{{$item}}</option>
            @endforeach
          </select>        
          <span class="absolute start-3 top-3 -translate-y-1/2 text-xs text-gray-700 transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-3 peer-focus:text-xs">
            {{$placeholder}}
        </span>
    </label>
    <label class="label">
        <span class="label-text-alt">
            @error($name)
                <span class="label-text-alt text-error">{{$message}}</span>
            @enderror
        </span>
    </label>
</div>