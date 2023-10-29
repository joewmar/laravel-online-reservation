@php
  $totalPrice = 0;
  $arrStatus = [1 => 'Confirmed', 2 => 'Check-in', 3 =>'Check-out'];
  $uinfo = [
    "first_name" => $user->first_name ?? '',
    "last_name" =>  $user->last_name ?? '',
    "age" => $user->age ?? '',
    "country"  => $user->country ?? '',
    "email"  => $user->email ?? '',
    "nationality" => $user->nationality ?? '',
    "contact" => $user->contact ?? '',
    'valid_id' => $user->valid_id ?? '',
    'senior_count' => '',
    'downpayment' => '',
    'cinamount' => '',
  ];
  if(session()->has('nwrinfo')){
    $ss = decryptedArray(session('nwrinfo'));

    if(isset($ss['secount'])) $uinfo['senior_count'] = $ss['secount'];
    if(isset($ss["first_name"], $ss["last_name"], $uinfo["age"])){
      $uinfo["first_name"] = $ss['fn'] ?? '';
      $uinfo["last_name"] =  $ss['ln'] ?? '';
      $uinfo["age"] = $ss['age'] ?? '';
      $uinfo["country"]  = $ss['ctct'] ?? '';
      $uinfo["email"]  = $ss['eml'] ?? '';
      $uinfo["nationality"] = $ss['ntnlt'] ?? '';
      $uinfo["contact"] = $ss['ctry'] ?? '';
      $uinfo['valid_id'] = $ss['vid'] ?? '';  
    }    
    
    if(isset($ss['secount'])) $uinfo['senior_count'] = $ss['secount'];
    if(isset($ss['dwnpy'])) $uinfo['downpayment'] = $ss['dwnpy'];
    elseif(isset($ss['cinpy'])) $uinfo['cinamount'] = $ss['cinpy'];
    
  }
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
                  <x-input type="number" name="age" id="age" min="10" placeholder="Age" value="{{$uinfo['age']}}" />
                  <x-datalist-input id="country" name="country" placeholder="Country" :lists="$countries" value="{{$uinfo['country']}}" />
                  <x-datalist-input id="nationality" name="nationality" placeholder="Nationality" :lists="$nationality" value="{{$uinfo['nationality']}}" />
                  <x-input type="email" name="email" id="email" placeholder="Contact Email" value="{{$uinfo['email']}}" />
                  <x-input type="tel" name="contact" id="contact" placeholder="Contact Number" value="{{$uinfo['contact']}}" noRequired />
                </div>
                <div class="flex flex-col-reverse md:flex-col w-full md:w-96 p-5" x-data="{pay: '{{!empty($uinfo['cinamount']) ? 'partial' : 'full'}}', senior: {{!empty($uinfo['senior_count']) ? 'true' : 'false'}}}">
                  <div class="mt-5 md:mt-0">
                    <h2 class="text-lg font-medium mb-5">New Status: {{$arrStatus[$status]}}</h2>
                    @if($status == 1)
                    <x-input type="number" name="dyamount" id="downpayment" min="1000" placeholder="Downpayment" value="{{$uinfo['downpayment']}}" />
                    @elseif($status == 2)
                        <div class="mb-5">
                          <input id="discount" x-model="senior" name="hs" type="checkbox" class="checkbox checkbox-secondary" />
                          <label for="discount" class="ml-4 font-semibold">Have Senior Citizen?</label>
                        </div>
                        <template x-if="senior">
                            <div class="mt-3">
                                <x-input type="number" name="senior_count" id="senior_count" placeholder="Count of Senior Guest" value="{{$uinfo['senior_count'] ?? 0}}" />
                            </div>
                        </template>
                        <div class="py-3 space-x-2">
                            <input type="radio" x-model="pay" id="partial" name="cnpy" class="radio radio-primary" value="partial" />
                            <label for="partial">Partial</label>
                            <input type="radio" x-model="pay" id="full_payment" name="cnpy" class="radio radio-primary" value="fullpayment" />
                            <label for="full_payment">Full Payment</label>
                            @error('cnpy')
                              <span>{{$message}}</span>
                            @enderror
                            <template x-if="pay == 'partial'">
                                <div class="my-3">
                                    <x-input name="cinamount" id="amountcinp" placeholder="Amount" value="{{$uinfo['cinamount']}}"  /> 
                                </div>
                            </template>
                        </div>
                        
                    @endif
                  </div>
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
              <x-input type="number" name="age" id="age" min="10" placeholder="Age" value="{{$uinfo['age']}}" />
              <x-datalist-input id="country" name="country" placeholder="Country" :lists="$countries" value="{{$uinfo['country']}}" />
              <x-datalist-input id="nationality" name="nationality" placeholder="Nationality" :lists="$nationality" value="{{$uinfo['nationality']}}" />
              <x-input type="email" name="email" id="email" placeholder="Contact Email" value="{{$uinfo['email']}}" />
              <x-input type="tel" name="contact" id="contact" placeholder="Contact Number" value="{{$uinfo['contact']}}" noRequired />
              <input type="hidden" name="uid" value="{{encrypt($user->id)}}">
            </div>
            <div class="flex flex-col-reverse md:flex-col w-full md:w-96 p-5" x-data="{pay: '{{!empty($uinfo['cinamount']) ? 'partial' : 'full'}}', senior: {{!empty($uinfo['senior_count']) ? 'true' : 'false'}}}">
              <div class="mt-5 md:mt-0">
                <h2 class="text-lg font-medium mb-5">New Status: <span class="font-normal">{{$arrStatus[$status]}}</span></h2>
                @if($status == 1)
                    <x-input type="number" name="dyamount" id="downpayment" min="1000" placeholder="Downpayment" value="{{$uinfo['downpayment']}}" />
                @elseif($status == 2)
                    <div class="mb-5">
                      <input id="discount" x-model="senior" name="hs" type="checkbox" class="checkbox checkbox-secondary" />
                      <label for="discount" class="ml-4 font-semibold">Have Senior Citizen?</label>
                    </div>
                    <template x-if="senior">
                        <div class="mt-3">
                            <x-input type="number" name="senior_count" id="senior_count" placeholder="Count of Senior Guest" value="{{$uinfo['senior_count'] ?? 0}}" />
                        </div>
                    </template>
                    <div class="py-3 space-x-2">
                        <input type="radio" x-model="pay" id="partial" name="cnpy" class="radio radio-primary" value="partial" />
                        <label for="partial">Partial</label>
                        <input type="radio" x-model="pay" id="full_payment" name="cnpy" class="radio radio-primary" value="fullpayment" />
                        <label for="full_payment">Full Payment</label>
                        @error('cnpy')
                          <span>{{$message}}</span>
                        @enderror
                        <template x-if="pay == 'partial'">
                            <div class="my-3">
                                <x-input name="cinamount" id="amountcinp" placeholder="Amount" value="{{$uinfo['cinamount']}}"  /> 
                            </div>
                        </template>
                    </div>
                    
                @endif
              </div>
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
