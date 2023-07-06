@props(['id' => '', 'name' => '', 'placeholder' => '', 'rows' => '5', 'value' => '', 'disabled' => false])
<div class="form-control w-full {{$disabled ? 'disabledAll opacity-50' : 'opacity-100'}}">
    @if($disabled)
        <textarea id="{{$id}}" name="{{$name}}" class="w-full rounded-lg border-base-200 focus-within:border-primary focus-within:ring-1 focus-within:ring-primary p-3 text-sm" placeholder="{{$placeholder}}" rows="{{$rows}}" name="{{$name}}" id="{{$id}}" disabled >{{old($name) == '' ? '' : old($name)}}{{$value == '' ? '' : $value}}</textarea>
    @else
        <textarea id="{{$id}}" name="{{$name}}" class="w-full rounded-lg border-base-200 focus-within:border-primary focus-within:ring-1 focus-within:ring-primary p-3 text-sm" placeholder="{{$placeholder}}" rows="{{$rows}}" name="{{$name}}" id="{{$id}}" >{{old($name) == '' ? '' : old($name)}}{{$value == '' ? '' : $value}}</textarea>
    @endif
    <label class="label">
    <span class="label-text-alt">
        @error($name)
            <span class="label-text-alt text-error">{{$message}}</span>
        @enderror
    </span>
    </label>
</div>