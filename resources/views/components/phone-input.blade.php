@props(['type' => 'tel', 'value' => '', 'id' => 'phone', 'name' => 'contact', 'placeholder' => 'Contact', 'value' => '', 'disabled' => false] )
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
@endpush
<div class="form-control w-full {{$disabled ? 'disabledAll opacity-50' : 'opacity-100'}}">
    <label for="{{$id}}" class=" w-full relative flex justify-start rounded-md border border-base-200 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary @error($name) ring-1 ring-error border-error @enderror ">
        @if($disabled)
            <input type="{{$type}}" id="{{$id}}" name="{{$name}}" {{$attributes->merge(['class' => 'w-full input input-primary peer border-none bg-transparent focus:border-transparent focus:outline-none focus:ring-0'])}} value="{{$value == '' ? old($name) : $value}}" {{ $attributes }} disabled/>
        @else
            <input type="{{$type}}" id="{{$id}}" name="{{$name}}" {{$attributes->merge(['class' => 'w-full input input-primary peer border-none bg-transparent focus:border-transparent focus:outline-none focus:ring-0'])}} value="{{$value == '' ? old($name) : $value}}" {{ $attributes }}  />
        @endif
        <span id="{{$id}}" class="pointer-events-none absolute start-2.5 top-0 -translate-y-1/2 bg-white p-0.5 text-xs text-neutral transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-0 peer-focus:text-xs">
            {{$placeholder}}
        </span>
    </label>
    <label class="label">
        <span class="label-text-alt">
            @error($name)
                <span class="mb-5 label-text-alt text-error">{{$message}}</span>
            @enderror
        </span>
    </label>
</div>
@push('scripts')
    <script>
    const phoneInputField = document.querySelector("#phone");
    const phoneInput = window.intlTelInput(phoneInputField, {
        utilsScript:
        "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
    });
    </script>
@endpush