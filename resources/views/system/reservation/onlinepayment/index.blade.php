<x-system-layout :activeSb="$activeSb">
    <x-system-content title="">
        {{-- User Details --}}
        <div class="w-full p-8 sm:flex sm:space-x-6">
            <div class="flex-shrink-0 mb-6 h-15 sm:h-32 w-15 sm:w-32 sm:mb-0">
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
        <div class="flex px-8 justify-between">
            <h1 class="font-bold text-xl">Payment: <span class="font-normal text-xl">{{$r_list->payment_method}}</span></h1>
            <label for="fpmdl" class="btn btn-warning btn-sm">
                Force Payment
            </label>
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
                    <x-passcode-modal title="Force Payment Confirmation" id="pssmdl" formId="frcpf" loader/>        

                </form>
            </x-modal >
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
                                        <div class="text-4xl text-primary flex items-center space-x-3">
                                            <span><i class="fa-regular fa-face-smile"></i></span>
                                            <span class="text-xl font-bold">Approved</span>
                                        </div>
                                    @endif
                                    @if($item->approval === 0)
                                    <div class="text-4xl text-error flex items-center space-x-3">
                                        <span><i class="fa-regular fa-face-frown"></i></i></span>
                                            <span class="text-xl font-bold">Disapproved</span>
                                        </div>
                                    @endif
                                    @if($item->approval === 3)
                                    <div class="text-4xl text-error flex items-center space-x-3">
                                        <span><i class="fa-regular fa-face-frown"></i></i></span>
                                            <span class="text-xl font-bold">Partial Approve</span>
                                        </div>
                                    @endif
                                    <div class="flex justify-between items-center">
                                        <p class="text-neutral font-bold text-2xl">Payment information {{$loop->index+1}}</p>
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
                                        <p class="text-neutral text-xl"><strong>Payer Name: </strong>{{$item->payment_name}}</p>
                                        <p class="text-neutral text-xl"><strong>Reference No.: </strong>{{$item->reference_no}}</p>
                                        <p class="text-neutral text-xl"><strong>Amount: </strong>{{$item->amount}}</p>
                                    </div>
                                    <div class="flex space-x-1 mt-5" id="{{!empty($item->approval) ? 'disabledAll' : ''}}">
                                            <label for="approve{{$item->id}}" class="btn btn-info btn-sm" {{!empty($item->approval) ? 'disabled' : ''}}>Approve</label>
                                            <label for="disaprove{{$item->id}}"class="btn btn-error btn-sm" {{!empty($item->approval) ? 'disabled' : ''}}>Disapprove</label>
                                        @if(empty($item->approval))       
                                            <x-modal id="approve{{$item->id}}" title="Approve for Payment Name: {{$item->payment_name}}" type="YesNo" formID="approve-form{{$item->id}}" noBottom>
                                                <h1 class="text-xl text-neutral">Verified?</h1>
                                                <form id="approve-form{{$item->id}}" action="{{route('system.reservation.online.payment.store', encrypt($item->id))}}" method="POST">
                                                    @csrf
                                                    <div x-data="{category: '{{$item->amount > 1000 ? 'full' : 'partial'}}'}" class="my-3">
                                                        <input id="cat1" class="my-2 radio radio-primary radio-sm" name="type" x-model="category" type="radio" value="partial" />
                                                        <label :aria-checked="category == 'downpayment'" :class="category == 'downpayment' ? 'mr-5 text-primary' : 'mr-5'" for="cat1" class="my-5">Partial Approve</label>  
                                                        <input id="cat2" class="my-2 radio radio-primary radio-sm" name="type" x-model="category" type="radio" value="full" />
                                                        <label :aria-checked="category == 'cinpayment'" :class="category == 'cinpayment' ? 'mr-5 text-primary' : 'mr-5'" for="cat2" class="my-5">Full Approve</label>  
                                                      </div>
                                                    <x-input type="number" name="amount" id="amount" placeholder="Total Amount Approve" value="{{$total}}" />
                                                </form>
                                            </x-modal>
                                            <form id="disaprove-form{{$item->id}}" action="{{route('system.reservation.online.payment.disaprove', encrypt($item->id))}}" method="POST" >
                                                @csrf
                                                <x-modal id="disaprove{{$item->id}}" title="Why disapprove of Payment Name: {{$item->payment_name}}?" formID="disaprove-form{{$item->id}}" type="YesNo" loader=true noBottom>
                                                    <x-textarea type="text" name="reason" id="reason" placeholder="Reason" />
                                                </x-modal>
                                            </form>

                                        @endif
                                    </div>
                                </div>
                                <div class="w-72 rounded">
                                    <img src="{{route('private.image', ['folder' => explode('/', $item->image)[0], 'filename' => explode('/', $item->image)[1]])}}" alt="Payment Receipt of {{$r_list->userReservation->name()}}">
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