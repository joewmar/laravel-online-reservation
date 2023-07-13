{{-- @php
    $reservation = [];
    if(request()->exists('cin', 'cout', 'at', 'px') ){
      $reservation = [
        "cin" => request()->get('cin'),
        "cout" => request()->get('cout'),
        "px" => request()->get('px'),
        "at" => request()->get('at'),
      ];
    }
@endphp --}}
<x-landing-layout>
  <div class="flex w-full justify-center">
    <div class="card w-full md:w-[40%] bg-base-100 rounded-box my-10">
      <div class="card-body">
        <h2 class="mb-1 font-bold text-xl md:text-3xl text-center">Complete the other information of {{$user_info->first_name}} {{$user_info->last_name}}</h2>

      {{-- @if(request()->exists('cin', 'cout', 'at', 'px', 'ck'))
        <form action="{{ route('create', $reservation) }}" method="post">
      @else --}}
        <form action="{{ route('google.fillup.update', $user_info->google_id) }}" method="post">
      {{-- @endif --}}
          @csrf
          @method('PUT')
          {{-- Birthday --}}
          <x-datetime-picker name="birthday" id="birthday" placeholder="Birthday" class="flatpickr-bithday" />
          {{-- Nationality--}}
          <x-select id="nationality" name="nationality" placeholder="Nationality" :value="$nationality" :title="$nationality" selected="{{old('nationality') ?? ''}}" />
          {{-- Country--}}
          <x-select id="country" name="country" placeholder="Country" :value="$countries" :title="$countries" selected="{{old('country') ?? ''}}" />
          {{-- Phone Number  --}}
          <x-input type="number" name="contact" placeholder="Phone Number"/>
          <div class="form-control mt-6">
              <button type="submit" class="btn btn-primary w-full">Save</button>
          </div>
        </form>
      </div>
      </div>
    </div>
  </div>


</x-landing-layout>

