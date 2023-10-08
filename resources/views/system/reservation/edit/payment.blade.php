<x-system-layout :activeSb="$activeSb">
    <x-system-content title="" back=true>
        {{-- User Details --}}
        {{-- <div class="w-full text-center">
            <span x-show="!document.querySelector('[x-cloak]')" class="loading loading-spinner loading-lg text-primary"></span>
        </div> --}}
        <section>
        <div class="w-full py-8 sm:flex sm:space-x-6 px-20">
            <div class="hidden md:flex flex-shrink-0 mb-6 h-15 sm:h-32 w-15 sm:w-32 sm:mb-0">
                @if(filter_var($r_list->userReservation->avatar ?? '', FILTER_VALIDATE_URL))
                    <img src="{{$r_list->userReservation->avatar}}" alt="" class="object-cover object-center w-full h-full rounded">
                @elseif($r_list->userReservation->avatar ?? false)
                    <img src="{{asset('storage/'. $r_list->userReservation->avatar)}}" alt="" class="object-cover object-center w-full h-full rounded">
                @else
                    <img src="{{asset('images/avatars/no-avatar.png')}}" alt="" class="object-cover object-center w-full h-full rounded">
                @endif
            </div>            
            <div class="flex flex-col space-y-4">
                <div>
                    <h2 class="text-2xl font-semibold">{{$r_list->userReservation->name()}}</h2>
                    <span class="block text-sm text-neutral">{{$r_list->userReservation->age()}} years old from {{$r_list->userReservation->country}}</span>
                    <span class="text-sm text-neutral">{{$r_list->userReservation->nationality}}</span>
                </div>
                <div class="space-y-1">
                    <span class="flex items-center space-x-2">
                        <i class="fa-regular fa-envelope w-4 h-4"></i>
                        <span class="text-neutral">{{$r_list->userReservation->email}}</span>
                    </span>
                    <span class="flex items-center space-x-2">
                        <i class="fa-solid fa-phone w-4 h-4"></i>
                        <span class="text-neutral">{{$r_list->userReservation->contact}}</span>
                    </span>
                </div>
            </div>
        </div>
        <div class="flex justify-between items-center space-x-1 px-20">
            <div>
                @if(request()->has('tab') && request('tab') === 'CINP')
                    <div class="text-neutral"><strong>Check-in Payment: </strong>₱ {{number_format($r_list->checkInPayment(), 2)}}</div>
                @else
                    <div class="text-neutral"><strong>Previous Downpayment: </strong>₱ {{number_format($r_list->downpayment(), 2)}}</div>
                @endif
            </div>
            <div class="tabs tabs-boxed bg-transparent items-center">
                <a href="{{route('system.reservation.edit.payment', encrypt($r_list->id))}}" class="tab {{request()->has('tab') && request('tab') === 'CINP' ? '' : 'tab-active'}}">Downpayment</a> 
                <a href="{{route('system.reservation.edit.payment', [encrypt($r_list->id), 'tab=CINP' ])}}" class="tab {{request()->has('tab') && request('tab') === 'CINP' ? 'tab-active' : ''}}">Check-in Payment</a> 
            </div>
        </div>
        <div class="divider"></div>
        @if(request()->has('tab') && request('tab') === 'CINP')
            <article x-data="{pay: '{{$r_list->balance() === 0 ? 'fullpayment' : 'partial'}}', senior: {{isset($r_list->transaction['payment']['discountPerson']) ? 'true' : 'false'}} }" class="w-full flex justify-center px-20">
                <form id="cinpform" action="{{route('system.reservation.update.cinpayment', encrypt($r_list->id))}}" method="POST" class="w-96">
                    @csrf
                    @method('PUT')
                    <h2 class="text-2xl mb-5 font-bold">Edit Check-in Payment</h2>
                    <div class="mb-5">
                        <input id="discount" x-model="senior" name="hs" type="checkbox" class="checkbox checkbox-secondary" />
                        <label for="discount" class="ml-4 font-semibold">Have Senior Citizen?</label>
                    </div>
                    <template x-if="senior">
                        <div class="mt-3">
                            <x-input type="number" name="senior_count" id="senior_count" placeholder="Count of Senior Guest" value="{{$r_list->transaction['payment']['discountPerson'] ?? 0}}" />
                        </div>
                    </template>
                    <div class="py-3 space-x-2">
                        <input type="radio" x-model="pay" id="partial" name="cnpy" class="radio radio-primary" value="partial" />
                        <label for="partial">Partial</label>
                        <input type="radio" x-model="pay" id="full_payment" name="cnpy" class="radio radio-primary" value="fullpayment" />
                        <label for="full_payment">Full Payment</label>
                        <template x-if="pay == 'partial'">
                            <div class="my-3">
                                <x-input name="amount" id="amountcinp" placeholder="Amount" value="{{$r_list->checkInPayment() ?? ''}}" max="{{$r_list->balance()}}" /> 
                            </div>
                        </template>
                    </div>
                    <label for="cinpymdl" class="btn btn-primary btn-block">Save</label>
                    <x-passcode-modal title="Edit Check-in Payment Confirmation" id="cinpymdl" formId="cinpform" />
                </form>
            </article>
        @else
          <article class="w-full flex justify-center px-20">
            <form id="dyform" action="{{route('system.reservation.update.downpayment', encrypt($r_list->id))}}" method="POST" class="w-96">
                @csrf
                @method('PUT')
                <h2 class="text-2xl mb-5 font-bold">Edit Downpayment</h2>
                <x-input name="amount" id="amountdy" placeholder="Amount" value="{{$r_list->downpayment() ?? ''}}" min="1000" max="{{$r_list->balance()}}" /> 
                <label for="dymdl" class="btn btn-primary btn-block">Save</label>
                <x-passcode-modal title="Edit Downpayment Confirmation" id="dymdl" formId="dyform" />
            </form>
          </article>
        @endif
    </section>
    </x-system-content>
</x-system-layout>