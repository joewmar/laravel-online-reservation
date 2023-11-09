<x-system-layout :activeSb="$activeSb">
    <x-system-content title="" back="{{route('system.reservation.show', encrypt($r_list->id))}}">
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
            <article x-data="checkIn" class="w-full flex justify-center px-20">
                <form x-data="{pay: {{$r_list->checkInPayment()}}, senior: {{isset($r_list->transaction['payment']['discountPerson']) ? 'true' : 'false'}}, scount: {{$r_list->transaction['payment']['discountPerson'] ?? 0}}, st: {{$r_list->getServiceTotal()}}, dw: {{$r_list->downpayment()}}, ra: {{$r_list->getRoomAmount()}}, cintotal: {{$r_list->getTotal()}}, balance: {{$r_list->balance()}}, change: {{$r_list->refund()}}, dstd: 0}" id="cinpform" action="{{route('system.reservation.update.cinpayment', encrypt($r_list->id))}}" method="POST" class="w-96">
                    @csrf
                    @method('PUT')
                    <h2 class="text-xl md:text-2xl mb-5 font-bold">Edit Check-in Payment</h2>
                    <div class="mb-5 mt-3">
                        <div class="mb-3">
                            <p class="text-sm"><strong>Service Cost: </strong> <span x-text="st > 0 ? '₱ ' + st.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                            <p class="text-sm"><strong>Rate Amount per Person: </strong> {{$r_list->getRoomAmount(false, true) > 0 ? '₱ ' . number_format($r_list->getRoomAmount(false, true), 2) : 'None'}}</p>
                            <template x-if="senior">
                                <div>
                                    <p class="text-sm"><strong>Senior / PWD Guest: </strong> <span x-text="scount > 0 ? scount  : 'None' "></span></p>
                                    <p class="text-sm"><strong>Rate Discounted per Person: </strong> <span x-text="dstd > 0 ? '₱ ' + dstd.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                                    <p class="text-sm"><strong>Total Rate: </strong> <span x-text="ra > 0 ? '₱ ' + ra.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                                </div>
                            </template>
                            <p class="text-sm"><strong>Total Cost: </strong> <span x-text="cintotal > 0 ? '₱ ' + cintotal.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                            <p class="text-sm"><strong>Downpayment: </strong> <span x-text="dw > 0 ? '₱ ' + dw.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                            <p class="text-sm"><strong>Balance: </strong> <span x-text="balance > 0 ? '₱ ' + balance.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None' "></span></p>
                        </div>
                        <input id="discount" @change="CIN()" name="hs" x-model="senior" type="checkbox" class="checkbox checkbox-secondary checkbox-sm md:checkbox-md" />
                        <label for="discount" class="text-sm md:text-lg ml-4 font-semibold">Have Senior / PWD ?</label>
                        <template x-if="senior">
                            <div class="my-3">
                                <x-input x-model="scount" type="number" name="senior_count" id="senior_count" placeholder="Count of Guest" value="{{old('senior_count')}}" input="CIN()" />
                            </div>
                        </template>
                    </div>
                    <x-input x-model="pay" type="number" name="amount" id="amount" placeholder="Amount" input="CIN()" />
                    <div x-show="change > 0" class="text-error" x-text="'Change: ₱ ' + change.toLocaleString('en-US', {maximumFractionDigits:2})"></div>
                    <label for="cinpymdl" class="btn btn-primary btn-block">Save</label>
                    <x-passcode-modal title="Edit Check-in Payment Confirmation" id="cinpymdl" formId="cinpform" />
                </form>
            </article>
            @push('scripts')
                <script>
                    document.addEventListener('alpine:init', () => {
                        Alpine.data('checkIn', () => ({
                            pay: {{$r_list->checkInPayment()}}, 
                            senior: {{$r_list->countSenior() > 0 ? 'true' : 'false'}}, 
                            scount: {{$r_list->countSenior() > 0 ? $r_list->countSenior() : 0}},
                            st: {{$r_list->getServiceTotal()}},
                            dw: {{$r_list->downpayment()}},
                            ra: {{$r_list->getRoomAmount(false)}},
                            rap: {{$r_list->getRoomAmount(false, true)}},
                            cintotal: {{$r_list->getTotal()}},
                            balance: {{$r_list->balance()}},
                            change: {{$r_list->balance()}},
                            discounted(amount = 0, rate = 20) {
                                let d = 0;
                                d = (rate / 100);
                                d = amount * d;
                                d = amount - d;
                                return d;
                            },
                            CIN() {
                                let dstd = this.discounted(this.rap);
                                let rnum = 0;
                                if(this.senior == true && this.scount != 0){
                                    this.dstd = dstd;
                                }
                                for(i = 1; i <= {{$r_list->pax}}; i++){
                                    if(i <= this.scount) rnum += parseFloat(this.dstd) ;
                                    else rnum += this.rap;
                                }
                                this.ra = rnum;
                                this.cintotal = this.st + this.ra
                                this.balance = this.cintotal - this.dw;
                                let orig_balance = this.balance;
                                if(this.balance >= this.pay && !(Math.sign(this.pay) == -1)) this.balance = Math.abs(this.balance - this.pay);
                                else this.balance = 0;

                                if(this.balance > this.change) this.change = 0;


                                if(orig_balance < this.pay) this.change = Math.abs(orig_balance - this.pay);
                                else this.change = 0;

                                
                            },
                        }));
                    });
                </script>
            @endpush
        @else
          <article class="w-full flex justify-center px-20">
            <form id="dyform" action="{{route('system.reservation.update.downpayment', encrypt($r_list->id))}}" method="POST" class="w-96">
                @csrf
                @method('PUT')
                <h2 class="text-xl md:text-2xl mb-5 font-bold">Edit Downpayment</h2>
                <x-input name="amount" id="amountdy" placeholder="Amount" value="{{$r_list->downpayment() ?? ''}}" min="1000" max="{{$r_list->balance()}}" /> 
                <label for="dymdl" class="btn btn-primary btn-block">Save</label>
                <x-passcode-modal title="Edit Downpayment Confirmation" id="dymdl" formId="dyform" />
            </form>
          </article>
        @endif
    </section>
    </x-system-content>
</x-system-layout>