@props(['id' => '', 'name' => '', 'placeholder' => '', 'rows' => '5', 'value' => '', 'disabled' => false])
<div class="form-control w-full {{$disabled ? 'disabledAll opacity-50' : 'opacity-100'}}">
    @if($disabled)
        <textarea id="{{$id}}" name="{{$name}}" class="w-full rounded-lg border-gray-300 focus-within:border-primary focus-within:ring-1 focus-within:ring-primary p-3 text-sm @error($name) border-error @enderror" placeholder="{{$placeholder}}" rows="{{$rows}}" name="{{$name}}" id="{{$id}}" disabled >@if(!empty($value)){{$value}}@else{{old($name)}}@endif</textarea>
    @else
        <textarea id="{{$id}}" name="{{$name}}" class="w-full rounded-lg border-gray-300 focus-within:border-primary focus-within:ring-1 focus-within:ring-primary p-3 text-sm @error($name) border-error @enderror" placeholder="{{$placeholder}}" rows="{{$rows}}" name="{{$name}}" id="{{$id}}" >@if(!empty($value)){{$value}}@else{{old($name)}}@endif</textarea>
    @endif 
    <label class="label">
    <span class="label-text-alt">
        @error($name)
            <span class="label-text-alt text-error">{{$message}}</span>
        @enderror
    </span>
    </label>
</div>