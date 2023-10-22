<x-system-layout :activeSb="$activeSb">
    <x-loader />
    <x-system-content title="">
        {{-- User Details --}}
        <div class="px-8 mb-5">
            <x-profile :rlist="$r_list" />
        </div>
        <div class="block md:flex px-8 items-start justify-between">
            <h1 class="font-bold text-sm md:text-xl">Payment: <span class="font-normal text-sm md:text-xl">{{$r_list->payment_method}}</span></h1>
            <div class="flex flex-col items-end">
                <label for="fpmdl" class="btn btn-warning btn-sm" {{$r_list->downpayment() >= 1000 ? 'disabled' : ''}}>
                    Force Payment
                </label>
                @if($r_list->downpayment() >= 1000)
                    <div class="text-error text-[10px] md:text-sm text-right">Unable because your downpayment is more than ₱ 1,000.</div>
                @endif 
            </div>
            @if (!($r_list->downpayment() >= 1000))
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
                                    <div class="text-2xl md:text-4xl text-error flex items-center space-x-3">
                                        <span><i class="fa-regular fa-face-frown"></i></i></span>
                                            <span class="text-sm md:text-xl font-bold">Partial Approve</span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between items-center">
                                        <p class="text-neutral font-bold text-lg md:text-2xl">Payment information {{$loop->index+1}}</p>
                                        <div class="dropdown dropdown-end">
                                            <label tabindex="0" class="btn btn-circle btn-ghost btn-xs text-blue-500">
                                              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-4 h-4 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </label>
                                            <div tabindex="0" class="card compact dropdown-content z-[1] shadow bg-base-100 rounded-box w-64">
                                              <div class="card-body">
                                                <h2 class="card-title text-sm">Payment information {{$loop->index+1}}</h2> 
                                                <p class="text-xs">This payment information will be automatically deleted on {{Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $item->created_at)->addMonth(1)->format('M j, Y')}}</p>
                                              </div>
                                            </div>
                                          </div>
                                    </div>
                                    <div class="mt-5">
                                        <p class="text-neutral text-sm md:text-xl"><strong>Payer Name: </strong>{{$item->payment_name}}</p>
                                        <p class="text-neutral text-sm md:text-xl"><strong>Reference No.: </strong>{{$item->reference_no}}</p>
                                        <p class="text-neutral text-sm md:text-xl"><strong>Amount: </strong>{{$item->amount}}</p>
                                    </div>
                                    <div class="flex space-x-1 mt-5" id="{{!empty($item->approval) ? 'disabledAll' : ''}}">
                                            <label for="approve{{$item->id}}" class="btn btn-info btn-sm" {{isset($item->approval) ? 'disabled' : ''}}>Approve</label>
                                            <label for="disaprove{{$item->id}}"class="btn btn-error btn-sm" {{isset($item->approval) ? 'disabled' : ''}}>Disapprove</label>
                                        @if(!isset($item->approval))       
                                            <x-modal id="approve{{$item->id}}" title="Approve for Payment Name: {{$item->payment_name}}" noBottom>
                                                <h1 class="text-xl text-neutral">Verified?</h1>
                                                <form x-data="{category: '{{$item->amount >= 1000 ? 'full' : 'partial'}}', py: '{{$total}}'}" id="approve-form{{$item->id}}" action="{{route('system.reservation.online.payment.store', encrypt($item->id))}}" method="POST">
                                                    @csrf
                                                    <div class="my-3">
                                                        <input id="cat1" class="my-2 radio radio-primary radio-sm" name="type" x-model="category" type="radio" value="partial" :disabled="py >= 1000" />
                                                        <label :aria-checked="category == 'partial'" :class="category == 'partial' ? 'mr-5 text-primary' : 'mr-5'" for="cat1" class="my-5">Partial Approve</label>  
                                                        <input id="cat2" class="my-2 radio radio-primary radio-sm" name="type" x-model="category" type="radio" value="full" />
                                                        <label :aria-checked="category == 'full'" :class="category == 'full' ? 'mr-5 text-primary' : 'mr-5'" for="cat2" class="my-5">Full Approve</label>  
                                                    </div>
                                                    <input @input="py >= 1000 ? category = 'full' : category = 'partial'" x-model="py" type="number" name="amount" id="amountpy" class="input input-primary w-full" placeholder="Total Amount Approve" />
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