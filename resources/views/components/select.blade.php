@props(['id' => '', 'name' => '', 'value', 'placeholder', 'selected' => '', 'title' => '', 'class' => '', 'disabled' => false, 'xModel' => ''])
<div class="form-control w-full {{$disabled ? 'disabledAll opacity-50' : 'opacity-100'}}">
    <label for="{{$id}}" class="w-full relative flex justify-start rounded-md border border-gray-400 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary @error($name) ring-1 ring-error border-error @enderror">
        @if($disabled)
            @if(empty($xModel))
                <select name="{{$name}}" id="{{$id}}" {{$attributes->merge(['class' => 'w-full select select-primary peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0'])}} disabled>
            @else
                <select x-model="{{$xModel}}" name="{{$name}}" id="{{$id}}" {{$attributes->merge(['class' => 'w-full select select-primary peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0'])}} disabled>
            @endif
        @else
            @if(empty($xModel))
                <select name="{{$name}}" id="{{$id}}" {{$attributes->merge(['class' => 'w-full select select-primary peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0'])}}>
            @else
                <select x-model="{{$xModel}}" name="{{$name}}" id="{{$id}}" {{$attributes->merge(['class' => 'w-full select select-primary peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0'])}}>
            @endif
        @endif
            <option value="" disabled selected>Please select</option>
            @foreach ($value as $key => $item)
                @if($selected == $item || $selected == $title[$key])
                    <option value="{{$item}}" selected>{{$title[$key]}}</option>
                @else
                    <option value="{{$item}}">{{$title[$key]}}</option>
                @endif
            @endforeach
        </select>        
        <span id="{{$id}}" class="pointer-events-none absolute start-2.5 top-0 -translate-y-1/2 bg-white p-0.5 text-xs text-neutral transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-0 peer-focus:text-xs">
            {{$placeholder}}
        </span>
    </label>
    <label class="label">
        <span class="label-text-alt">
            @error($name)
                <span class="label-text-alt text-red-500">{{$message}}</span>
            @enderror
        </span>
    </label>
</div>