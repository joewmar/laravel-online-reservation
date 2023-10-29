<x-system-layout :activeSb="$activeSb">
    <x-loader />
    <x-system-content title="" back="{{route('system.reservation.show', encrypt($r_list->id))}}">
        {{-- User Details --}}
        <div class="px-8 mb-5">
            <x-profile :rlist="$r_list" />
        </div>
        <div class="block md:flex px-8 items-start justify-between">
            <div>
                <h1 class="font-bold text-sm md:text-xl">Payment: <span class="font-normal text-sm md:text-xl">{{$r_list->payment_method}}</span></h1>
                @if($r_list->payment->whereNotNull('approval')->count() > 0)
                    <h2 class="font-bold text-sm md:text-xl">Attempt: <span class="font-normal">{{3 - $r_list->payment->whereNotNull('approval')->count() > 0 ? 3 - $r_list->payment->whereNotNull('approval')->count() : 'None'}}</span></h2>
                @endif
            </div>
            <div class="flex flex-col items-end">
                <label for="fpmdl" class="btn btn-warning btn-sm" {{$r_list->downpayment() >= 1000 || $r_list->status == 5 ? 'disabled' : ''}}>
                    Force Payment
                </label>
            </div>
            @if (!($r_list->downpayment() >= 1000) || $r_list->status == 5)
                <x-modal id="fpmdl" title="Force payment for {{$r_list->userReservation->name()}}">
                    <form id="frcpf" method="POST" action="{{route('system.reservation.online.payment.forcepayment.update', encrypt($r_list->id))}}" >
                        @csrf
                        @method('PUT')
                        <x-input type="number" name="amount" id="amount" placeholder="Amount" value="{{old('amount') ?? ''}}" />
                        <div class="modal-action">
                            <label for="pssmdl" class="btn btn-primary">
                                Force Payment
                            </label>
                        </div>
                        <x-passcode-modal title="Force Payment Confirmation" id="pssmdl" formId="frcpf" loader noBottom />        
                    </form>
                </x-modal > 
            @endif
        </div>
        <div class="divider px-8"></div>
        <div class="block">
            <article class="text-md tracking-tight text-neutral my-5 p-5 w-auto">
                @php $total = 0 @endphp
                    @forelse ($r_list->payment as $item)
                        @php $total += (double)$item->amount @endphp
                        @if($item->payment_method === $r_list->payment_method)
                            <div class="flex flex-col-reverse md:flex-row  justify-center items-center w-full h-full space-y-2 md:space-x-5">
                                <div class="w-96 rounded my-5">
                                    @if($item->approval === 1)
                                        <div class="text-2xl md:text-4xl text-primary flex items-center space-x-3">
                                            <span><i class="fa-regular fa-face-smile"></i></span>
                                            <span class="text-sm md:text-xl font-bold">Approved</span>
                                        </div>
                                    @endif
                                    @if($item->approval === 0)
                                    <div class="text-2xl md:text-4xl text-error flex items-center space-x-3">
                                        <span><i class="fa-regular fa-face-frown"></i></i></span>
                                            <span class="text-sm md:text-xl font-bold">Disapproved</span>
                                        </div>
                                    @endif
                                    @if($item->approval === 3)
                                    <div class="text-2xl md:text-4xl text-yellow-500 flex items-center space-x-3">
                                        <span><i class="fa-regular fa-face-meh"></i></span>
                                            <span class="text-sm md:text-xl font-bold">Partial Approve</span>
                                        </div>
                                    @endif                                        
                                    <p class="text-neutral font-bold text-lg md:text-xl">{{$item->payment_method}} Receipts at {{Carbon\Carbon::createFromFormat('Y-m-d h:i:s', $item->created_at)->setTimezone('Asia/Manila')->format('M j, Y')}}</p>
                                    <div class="mt-5">
                                        <p class="text-neutral text-sm md:text-xl"><strong>Payer Name: </strong>{{$item->payment_name}}</p>
                                        <p class="text-neutral text-sm md:text-xl"><strong>Reference No.: </strong>{{$item->reference_no}}</p>
                                        <p class="text-neutral text-sm md:text-xl"><strong>Amount: </strong>{{$item->amount}}</p>
                                    </div>
                                    <div class="flex space-x-1 mt-5" id="{{!empty($item->approval) ? 'disabledAll' : ''}}">
                                            <label for="approve{{$item->id}}" class="btn btn-info btn-sm" {{isset($item->approval) ? 'disabled' : ''}}>Approve</label>
                                            <label for="disaprove{{$item->id}}"class="btn btn-error btn-sm" {{isset($item->approval) ? 'disabled' : ''}}>Disapprove</label>
                                        @if(!isset($item->approval) || $r_list->status == 5)       
                                            <x-modal id="approve{{$item->id}}" title="Approve for Payment Name: {{$item->payment_name}}" noBottom>
                                                <h1 class="text-xl text-neutral">Verified?</h1>
                                                <form x-data="{category: '{{$item->amount >= 1000 ? 'full' : 'partial'}}', dy: {{$r_list->downpayment()}}, py: {{$item->amount}}, total: 0,
                                                    compute(){
                                                        this.total = Number(this.py) + Number(this.dy);
                                                        if(this.total >= 1000) this.category = 'full';
                                                        else this.category = 'partial';
                                                    }
                                                }" id="approve-form{{$item->id}}" action="{{route('system.reservation.online.payment.store', encrypt($item->id))}}" method="POST">
                                                    @csrf
                                                    <div class="my-3">
                                                        <input id="cat1" class="my-2 radio radio-primary radio-sm" name="type" x-model="category" type="radio" value="partial" :disabled="py >= 1000" />
                                                        <label :aria-checked="category == 'partial'" :class="category == 'partial' ? 'mr-5 text-primary' : 'mr-5'" for="cat1" class="my-5">Partial Approve</label>  
                                                        <input id="cat2" class="my-2 radio radio-primary radio-sm" name="type" x-model="category" type="radio" value="full" />
                                                        <label :aria-checked="category == 'full'" :class="category == 'full' ? 'mr-5 text-primary' : 'mr-5'" for="cat2" class="my-5">Full Approve</label>  
                                                    </div>
                                                    <div class="mb-3">
                                                        <p>Payment Amount: <span x-text="py > 0 ? '₱ ' + py.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None'"></span> </p>
                                                        <p>Downpayment: <span x-text="dy > 0 ? '₱ ' + dy.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None'"></span></p>
                                                        <p>Total: <span x-text="total > 0 ? '₱ ' + total.toLocaleString('en-US', {maximumFractionDigits:2}) : 'None'"></span></p>
                                                        <p>Required: <span x-text="total < 1000 ? '₱ ' + Number(1000 - dy).toLocaleString('en-US', {maximumFractionDigits:2}) : 'None'"></span></p>
                                                    </div>
                                                    <input type="number" @input="py >= 1000 ? category = 'full' : category = 'partial'; compute()" x-model="py" type="number" name="amount" id="amountpy" class="input input-primary w-full" placeholder="Total Amount Approve" />
                                                    <div class="modal-action">
                                                        <label for="aprvmdl{{$item->id}}"class="btn btn-primary">Approve</label>
                                                    </div>
                                                    <x-modal title="Do you want to disapprove?" id="aprvmdl{{$item->id}}" type="YesNo" formID="approve-form{{$item->id}}" loader=true noBottom >
                                                    </x-modal>
                                                </form>
                                            </x-modal>
                                            <x-modal id="disaprove{{$item->id}}" title="Why disapprove of Payment Name: {{$item->payment_name}}?" noBottom>
                                                <form id="disaprove-form{{$item->id}}" action="{{route('system.reservation.online.payment.disaprove', encrypt($item->id))}}" method="POST" >
                                                    @csrf
                                                    <x-disapprove-input :common="['Invalid Receipt', 'Invalid Transaction ID', 'Invalid Payer']" />
                                                    <div class="modal-action">
                                                        <label for="dspmdl{{$item->id}}"class="btn btn-error">Disapprove</label>
                                                    </div>
                                                    <x-modal title="Do you want to disapprove?" id="dspmdl{{$item->id}}" formID="disaprove-form{{$item->id}}" type="YesNo" loader=true noBottom >
                                                    </x-modal>
                                                </form>
                                            </x-modal>
                                        @endif
                                    </div>
                                </div>
                                <div class="w-52 md:w-72 rounded">
                                    <img src="{{route('private.image', ['folder' => explode('/', $item->image)[0], 'filename' => explode('/', $item->image)[1]])}}" alt="Payment Receipt of {{$r_list->userReservation->name()}}" class="object-fill">
                                </div>
                            </div>
                            <div class="divider"></div>
                        @endif
                    @empty
                    <div class="flex justify-center">
                        <h1 class="font-bold text-xl">No Payment Send</h1>
                    </div>
                    <div class="divider"></div>

                    @endforelse
            </article>
        </div>
    </x-system-content>
</x-system-layout>