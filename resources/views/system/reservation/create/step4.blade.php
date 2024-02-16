@php
  $totalPrice = 0;
  $uinfo = [
    "first_name" => $user->first_name ?? '',
    "last_name" =>  $user->last_name ?? '',
    "birthday" => $user->birthday ?? '',
    "email"  => $user->email ?? '',
    "nationality" => $user->nationality ?? '',
    "contact" => $user->contact ?? '',
    "country" => $user->country ?? '',
    'valid_id' => $user->valid_id ?? '',
    'senior_count' => 0,
    'downpayment' => 0,
    'cinamount' => 0,
  ];
  if(session()->has('nwrinfo')){
    $ss = decryptedArray(session('nwrinfo'));

      $uinfo["first_name"] = isset($ss['fn']) ? $ss['fn'] : $uinfo["first_name"];
      $uinfo["last_name"] =  isset($ss['ln']) ? $ss['ln'] : $uinfo["last_name"];
      $uinfo["birthday"] = isset($ss['bday']) ? $ss['bday'] : $uinfo["birthday"];
      $uinfo["contact"]  = isset($ss['ctct']) ? $ss['ctct'] : $uinfo["contact"];
      $uinfo["country"]  = isset($ss['ctry']) ? $ss['ctry'] : $uinfo["country"];
      $uinfo["email"]  = isset($ss['eml']) ? $ss['eml'] : $uinfo["email"];
      $uinfo["nationality"] = isset($ss['ntnlt']) ? $ss['ntnlt'] : $uinfo["nationality"];
      $uinfo['valid_id'] = isset($ss['vid']) ? $ss['vid'] : $uinfo["valid_id"];  
  
    if(isset($ss['secount'])) $uinfo['senior_count'] = $ss['secount'];
    if(isset($ss['dwnpy'])) $uinfo['downpayment'] = $ss['dwnpy'];
    if(isset($ss['cinpy'])) $uinfo['cinamount'] = $ss['cinpy'];

  }
  // dd($uinfo['cinamount']);
@endphp
<x-system-layout :activeSb="$activeSb">
  <x-system-content title="Add Book (Other Information)">
    <section class="my-10 p-5 w-full">
      <form x-data="searchRList" action="{{route('system.reservation.store.step.four')}}" method="POST" enctype="multipart/form-data">
        @csrf
        @if(!request()->has('uof') && empty($user))
          <div x-data="{ncus: {{$errors->any() || (session()->has('nwrinfo') && !isset(session('nwrinfo')['uid'])) ? 'true' : 'false'}}}">
            <div class="mb-5">
              <input x-effect="if(ncus) query = ''" id="ckcus" type="checkbox" x-model="ncus" name="ncus" class="checkbox checkbox-primary" />
              <label for="ckcus" class="ml-3 font-bold">New Customer</label>
            </div>
            <template x-if="ncus">
              <div class="block md:flex justify-around">
                <div class="w-full md:w-96">
                  <x-input name="first_name" id="first_name" placeholder="First Name" value="{{$uinfo['first_name']}}" />
                  <x-input name="last_name" id="last_name" placeholder="Last Name" value="{{$uinfo['last_name']}}" />
                  <x-birthday-input value="{{$uinfo['birthday']}}" />
                  <x-datalist-input id="country" name="country" placeholder="Country" :lists="$countries" value="{{$uinfo['country']}}" />
                  <x-datalist-input id="nationality" name="nationality" placeholder="Nationality" :lists="$nationality" value="{{$uinfo['nationality']}}" />
                  <x-input type="email" name="email" id="email" placeholder="Contact Email" value="{{$uinfo['email']}}" />
                  <x-input type="tel" name="contact" id="contact" placeholder="Contact Number" value="{{$uinfo['contact']}}" noRequired />
                </div>
                <div class="flex flex-col-reverse md:flex-col w-full md:w-96 p-5">
                  <div class="flex justify-center">
                    <x-drag-drop title="Validation ID" id="valid_id" name="valid_id" fileValue="{{!empty($uinfo['valid_id']) ? route('private.image', ['folder' => explode('/', $uinfo['valid_id'])[0], 'filename' => explode('/',$uinfo['valid_id'])[1]]) : ''}}" />
                  </div>
                </div>

              </div>
            </template>
            <template x-if="ncus">
              <div class="flex justify-end">
                <a href="{{route('system.reservation.create.step.three')}}" class="btn btn-ghost" @click="loader = true">Back</a>
                <button class="btn btn-primary" @click="loader = true">Next</button>
              </div>
            </template>
            <template x-if="!ncus">
              <div class="max-w-lg">
                <input x-model="query" @input="search()" type="search" class="w-full p-2 mb-4 input input-primary input-bordered" placeholder="Search Full Name">
                @error('uid')
                    <span class="block text-error">{{$message}}</span>
                @enderror
                <ul x-show="results.length != '0' && close == false" class="overflow-y-auto bg-base-200 rounded max-h-52">
                    <template x-for="(result, index)  in results">
                        <li class="my-3">
                            <a @click="loader = true" :href="result.link" x-text="result.title" class="btn btn-block hover:btn-primary" :class="result.title == ({{request()->has('uof') && !empty($user) ? $user->first_name . " " . $user->last_name : ''}}) ? 'btn-primary hover:btn-success' : '' "></a>
                        </li>
                    </template>
                </ul>
              </div>
            </template>
          </div>
        @endif
        @if(request()->has('uof') && !empty($user))
          <div class="block md:flex justify-around">
            <div class="w-full md:w-96">
              <div class="flex justify-between mb-3 items-center">
                <h1 class="font-medium">Search: {{$uinfo['first_name']}} {{$uinfo['last_name']}}</h1>
                <a href="{{route('system.reservation.create.step.four')}}" class="btn btn-ghost">Change</a>
              </div>
              <x-input name="first_name" id="first_name" placeholder="First Name" value="{{$uinfo['first_name']}}" />
              <x-input name="last_name" id="last_name" placeholder="Last Name" value="{{$uinfo['last_name']}}" />
              <x-birthday-input value="{{$uinfo['birthday']}}" />
              <x-datalist-input id="country" name="country" placeholder="Country" :lists="$countries" value="{{$uinfo['country']}}" />
              <x-datalist-input id="nationality" name="nationality" placeholder="Nationality" :lists="$nationality" value="{{$uinfo['nationality']}}" />
              <x-input type="email" name="email" id="email" placeholder="Contact Email" value="{{$uinfo['email']}}" />
              <x-input type="tel" name="contact" id="contact" placeholder="Contact Number" value="{{$uinfo['contact']}}" noRequired />
              <input type="hidden" name="uid" value="{{encrypt($user->id)}}">
            </div>
            <div class="flex flex-col-reverse md:flex-col w-full md:w-96 p-5">
              <div class="flex justify-center">
                <x-drag-drop title="Validation ID" id="valid_id" name="valid_id" fileValue="{{!empty($uinfo['valid_id']) ? route('private.image', ['folder' => explode('/', $uinfo['valid_id'])[0], 'filename' => explode('/',$uinfo['valid_id'])[1]]) : ''}}" />
              </div>
            </div>
          </div>
          <div class="flex justify-end">
            <a href="{{route('system.reservation.create.step.three')}}" class="btn btn-ghost" @click="loader = true">Back</a>
            <button class="btn btn-primary" @click="loader = true">Next</button>
          </div>
        @endif
      </form>
    </section>
  </x-system-content>
  @push('scripts')
    <script>
      		document.addEventListener('alpine:init', () => {
            Alpine.data('searchRList', () => ({
              query: '',
              results: [],
              users: [],
              close: false,
              search() {
                axios.get('{{route('system.reservation.create.step.three.search')}}', { params: { query: this.query } })
                  .then(response => {
                    this.results = response.data;
                      // console.log(response.data);
                  })
                  .catch(error => {
                    console.error(error);
                  });
                  this.close = false;  
              },

            }));
          });
    </script>
  @endpush
</x-system-layout>
