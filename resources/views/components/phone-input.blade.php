@props(['type' => 'tel', 'value' => '', 'id' => 'contact', 'name' => 'contact', 'placeholder' => 'Contact', 'value' => '', 'disabled' => false, 'noRequired' => false])

@php
    // Get the old input value for the phone number and country code
    $oldValue = old($name);
    $oldCountryCode = old($name.'_code');
@endphp

@push('styles')
    <style>
        /* .iti__flag {background-image: url("path/to/flags.png");}
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .iti__flag {background-image: url("path/to/flags@2x.png");}
        } */
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css">

@endpush

<div class="form-control w-full {{$disabled ? 'disabledAll opacity-50' : 'opacity-100'}}">
    <label for="{{$id}}" class="relative flex  justify-stretch rounded-md border border-gray-400 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary @error($name) ring-1 ring-error border-error @enderror ">
        <input type="{{$type}}" id="phone" name="{{$name}}" {{$attributes->merge(['class' => 'w-full input input-primary peer border-none bg-transparent focus:border-transparent focus:outline-none focus:ring-0'])}} value="{{$value == '' ? $oldValue : $value}}" {{ $attributes }} @if($disabled) disabled @endif/>
        <span id="{{$id}}" class="pointer-events-none absolute start-2.5 top-0 -translate-y-1/2 bg-base-100 p-0.5 text-xs text-neutral transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-0 peer-focus:text-xs">
            {{$placeholder}} @if(!$noRequired) <span class="text-error">*</span> @endif
        </span>
    </label>
    
    <input type="hidden" id="phone_code" name="{{$name}}_code" value="{{$oldCountryCode}}" {{$disabled ? 'disabled' : ''}} />

    <label class="label">
        <span class="label-text-alt">
            @error($name)
                <span class="mb-5 label-text-alt text-error">{{$message}}</span><br>
            @enderror
            @error($name.'_code')
                <span class="mb-5 label-text-alt text-error">{{$message}}</span>
            @enderror
        </span>
    </label>
</div>

@push('scripts')
    <!--<script type="module" src="{{Vite::asset('resources/js/phone.js')}}"></script>-->
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js"></script>

    <script>
        const phoneInputField = document.querySelector("#phone");
        // const phoneInput = window.intlTelInput(phoneInputField, {
        //     utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js",
        //  });
          const phoneInput = window.intlTelInput(phoneInputField, {
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js",
          });
                
        // Set the initial country code based on the old input value
        const initialCountryCode = document.querySelector("#phone_code").value;
        if (initialCountryCode) {
            phoneInput.setCountry(initialCountryCode);
        }
        
        @if(!empty($value))
            const selectedCountryData = phoneInput.getSelectedCountryData();
            const countryCode = selectedCountryData.iso2;

            document.querySelector("#phone_code").value = countryCode;            
        @endif
        // Listen for changes in the phone number input
        phoneInputField.addEventListener("countrychange", function () {
            const selectedCountryData = phoneInput.getSelectedCountryData();
            const countryCode = selectedCountryData.iso2;
        
            // Update the hidden input field with the selected country code
            document.querySelector("#phone_code").value = countryCode;
        });


    </script>
@endpush
