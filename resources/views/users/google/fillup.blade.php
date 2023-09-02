<x-landing-layout noFooter>
  <div class="flex w-full justify-center">
    <div class="card w-full md:w-[40%] bg-base-100 rounded-box my-10">
      <div class="card-body">
        <h2 class="mb-1 font-bold text-xl md:text-3xl text-center">Complete the other information of {{$guser['first_name']}} {{$guser['last_name']}} (Google)</h2>

      {{-- @if(request()->exists('cin', 'cout', 'at', 'px', 'ck'))
        <form action="{{ route('create', $reservation) }}" method="post">
      @else --}}
        <form action="{{ route('google.fillup.store')}}" method="post">
      {{-- @endif --}}
          @csrf
          {{-- Birthday --}}
          <x-input type="text" name="first_name" placeholder="First Name" value="{{$guser['first_name']}}" disabled />
          {{-- Last Name --}}
          <x-input type="text" name="last_name" placeholder="Last Name" value="{{$guser['last_name']}}" disabled/>
          {{-- Birthday --}}
          <x-birthday-input />
          {{-- Nationality--}}
          <x-datalist-input id="nationality" name="nationality" placeholder="Nationality" :lists="$nationality" value="{{old('nationality') ?? ''}}" />
          {{-- Country--}}
          <x-datalist-input id="country" name="country" placeholder="Country" :lists="$countries" value="{{old('country') ?? ''}}" />
          {{-- Phone Number  --}}
          <x-phone-input />
          <x-input type="email" name="email" placeholder="Contact Email" value="{{$guser['email']}}" disabled/>

          
          <div class="form-control mt-6">
              <button type="submit" class="btn btn-primary w-full">Save</button>
          </div>
        </form>
      </div>
      </div>
    </div>
  </div>


</x-landing-layout>

