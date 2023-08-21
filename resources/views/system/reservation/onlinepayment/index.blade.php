<x-system-layout :activeSb="$activeSb">
    <x-system-content title="">
        {{-- User Details --}}
        <div class="w-full p-8 sm:flex sm:space-x-6">
            <div class="flex-shrink-0 mb-6 h-15 sm:h-32 w-15 sm:w-32 sm:mb-0">
                @if(filter_var(auth('web')->user()->avatar ?? '', FILTER_VALIDATE_URL))
                    <img src="{{auth('web')->user()->avatar}}" alt="" class="object-cover object-center w-full h-full rounded">
                @elseif(auth('web')->user()->avatar ?? false)
                    <img src="{{asset('storage/'. auth('web')->user()->avatar)}}" alt="" class="object-cover object-center w-full h-full rounded">
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
        <div class="flex justify-between">
            <h1 class="font-bold text-xl">Payment: <span class="font-normal text-xl">{{$r_list->payment_method}}</span></h1>
            <label for="forcePayment_modal" class="btn btn-warning btn-sm">
                Force Payment
            </label>
            <x-modal id="forcePayment_modal" title="Force payment for {{$r_list->userReservation->name()}}">
                <form method="POST" action="{{route('system.reservation.online.payment.forcepayment.update', encrypt($r_list->id))}}" >
                    @csrf
                    @method('PUT')
                    <x-input type="number" name="amount" id="amount" placeholder="Amount" value="{{old('amount') ?? ''}}" />
                    <div class="modal-action">
                        <button @click="loader = true" class="btn btn-primary">
                            Force Payment
                        </button>
                    </div>
                </form>
            </x-modal >

        </div>
        <div class="divider"></div>
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
                                            <span class="text-xl font-bold">Approve</span>
                                        </div>
                                    @endif
                                    @if($item->approval === 0)
                                    <div class="text-4xl text-error flex items-center space-x-3">
                                        <span><i class="fa-regular fa-face-frown"></i></i></span>
                                            <span class="text-xl font-bold">Disaprove</span>
                                        </div>
                                    @endif
                                    <p class="text-neutral font-bold text-2xl">Payment information</p>
                                    <div class="mt-5">
                                        <p class="text-neutral text-xl"><strong>Payer Name: </strong>{{$item->payment_name}}</p>
                                        <p class="text-neutral text-xl"><strong>Reference No.: </strong>{{$item->reference_no}}</p>
                                        <p class="text-neutral text-xl"><strong>Amount: </strong>{{$item->amount}}</p>
                                    </div>
                                    <div class="flex space-x-1 mt-5" id="{{!empty($item->approval) ? 'disabledAll' : ''}}">
                                        @if(!empty($item->approval))
                                            <label class="btn btn-info btn-sm" disabled>Approve</label>
                                            <label class="btn btn-error btn-sm" disabled>Disaprove</label>
                                        @else
                                            <label for="approve{{$item->id}}" class="btn btn-info btn-sm">Approve</label>
                                            <label for="disaprove{{$item->id}}"class="btn btn-error btn-sm" >Disaprove</label>
                                        @endif
                                        <x-modal id="approve{{$item->id}}" title="Approve for Payment Name: {{$item->payment_name}}" type="YesNo" formID="approve-form{{$item->id}}" >
                                            <h1 class="text-xl text-neutral mb-5">Verified?</h1>
                                            <form id="approve-form{{$item->id}}" action="{{route('system.reservation.online.payment.store', encrypt($item->id))}}" method="POST">
                                                @csrf
                                                <x-input type="number" name="amount" id="amount" placeholder="Total Amount" value="{{$total}}" />
                                            </form>
                                        </x-modal>
                                        <x-modal id="disaprove{{$item->id}}" title="Why disapprove of Payment Name: {{$item->payment_name}}?" type="YesNo" loader=true>
                                            <form id="approve-form{{$item->id}}" action="{{route('system.reservation.online.payment.store', encrypt($item->id))}}" method="POST">
                                                @csrf
                                                <x-textarea type="text" name="reason" id="reason" placeholder="Reason" />
                                            </form>
                                        </x-modal>
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