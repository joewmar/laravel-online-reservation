@props(['id' => 'password', 'name' => 'password', 'placeholder' => 'Password', 'value' => '', 'validation' => false])
@if ($validation)
  <div x-data="passwordValidator{{Str::camel($id)}}()">
@endif
<div x-data="{ show{{Str::camel($id)}}: true }" class="form-control w-full mb-4">
  <label for="{{Str::camel($id)}}" class="relative flex rounded-md border border-gray-400 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary @error('password') ring-1 ring-error border-error @enderror" >
    @if ($validation)
      <input :type="show{{Str::camel($id)}} ? 'password' : 'text'" id="{{Str::camel($id)}}" name="{{$name}}" class="input input-primary peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0" placeholder="{{$placeholder}}" value="{{$value ?? ''}}" x-model="{{Str::camel($id)}}" @input="validatePassword{{Str::camel($id)}}" {{ $attributes }} />
    @else
      <input :type="show{{Str::camel($id)}} ? 'password' : 'text'" id="{{Str::camel($id)}}" name="{{$name}}" class="input input-primary peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0" placeholder="{{$placeholder}}" value="{{$value ?? ''}}" {{ $attributes }} />
    @endif
    <span class="pointer-events-none absolute start-2.5 top-0 -translate-y-1/2 bg-white p-0.5 text-xs text-gray-700 transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-0 peer-focus:text-xs" >
      {{$placeholder}}
    </span>
    <span class="absolute inset-y-0 end-0 grid w-10 place-content-center">
      <button type="button" @click="show{{Str::camel($id)}} = !show{{Str::camel($id)}}" class="rounded-full bg-transparent p-0.5 text-neutral hover:text-primary" >
        <span class="sr-only">Password</span>
        <i :class="show{{Str::camel($id)}} ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash'"></i>
      </button>
    </span>
  </label>
  @if($validation)
      <label x-show="validationMessage{{Str::camel($id)}} != ''   " class="label">
        <span x-text="validationMessage{{Str::camel($id)}}" class="label-text-alt text-error"></span>
      </label>
  @endif
  @error($name)
    <label class="label">
      <span class="label-text-alt text-error">{{$message}}</span>
    </label>
  @enderror
</div>
@if ($validation)
  @push('scripts')
    <script>
      function passwordValidator{{Str::camel($id)}}() {
        return {
          {{Str::camel($id)}}: "",
          validationMessage{{Str::camel($id)}}: "",
    
          validatePassword{{Str::camel($id)}} () {
            const {{Str::camel($id)}} = this.{{Str::camel($id)}};
    
            // Define your password requirements
            const symbolRegex{{Str::camel($id)}} = /[!@#$%^&*()_+{}\[\]:;<>,.?~\\/-]/;
            const minLength{{Str::camel($id)}} = 8;
            const numberRegex{{Str::camel($id)}} = /\d/;
            const letterRegex{{Str::camel($id)}} = /[a-zA-Z]/; // Add this regex for at least one letter
    
            // Check if the password meets the requirements
            const hasSymbol{{Str::camel($id)}} = symbolRegex{{Str::camel($id)}}.test({{Str::camel($id)}});
            const isMinLength{{Str::camel($id)}} = {{Str::camel($id)}}.length >= minLength{{Str::camel($id)}};
            const hasNumber{{Str::camel($id)}} = numberRegex{{Str::camel($id)}}.test({{Str::camel($id)}});
            const hasLetter{{Str::camel($id)}} = letterRegex{{Str::camel($id)}}.test({{Str::camel($id)}});
    
            if (hasSymbol{{Str::camel($id)}} && isMinLength{{Str::camel($id)}} && hasNumber{{Str::camel($id)}} && hasLetter{{Str::camel($id)}}) {
              this.validationMessage{{Str::camel($id)}} = "";
            }
            else{
              this.validationMessage{{Str::camel($id)}} = "The password must require at least 8 characters, one symbol, one number, and one letter.";
            }
          },
        };
      }
    </script>
  @endpush
  </div>
@endif

