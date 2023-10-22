
<x-landing-layout noFooter>
    <x-navbar :activeNav="$activeNav" type="plain"/>
    <x-full-content>
        <section class="px-10 md:px-20 pt-24">
            {{-- User Details --}}
            <a href="{{route('user.reservation.show', encrypt($r_list->id))}}" class="btn btn-ghost btn-circle">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="px-3 md:px-20">
                <x-profile :rlist="$r_list" />
                <div class="mt-5 flex justify-between items-end">
                    <div>
                        <h2 class="font-bold text-sm">Payment Method: <span class="font-normal">{{$r_list->payment_method}}</span></h2>
                        <h2 class="font-bold text-sm">Attempt: <span class="font-normal">{{3 - $r_list->payment->count()}}</span></h2>
                    </div>
                    @if($r_list->payment_method == "Gcash")
                        <a href="{{route('reservation.gcash', encrypt($r_list->id))}}" class="btn btn-primary btn-sm md:btn-md">Send</a>
                    @elseif($r_list->payment_method == "PayPal")
                        <a href="{{route('reservation.paypal', encrypt($r_list->id))}}" class="btn btn-primary btn-sm md:btn-md">Send</a>
                    @elseif($r_list->payment_method == "Bank Transfer")
                        <a href="{{route('reservation.bnktr', encrypt($r_list->id))}}" class="btn btn-primary btn-sm md:btn-md">Send</a>
                    @endif
                </div>
                <div class="divider"></div>
                <article class="text-md tracking-tight text-neutral my-5 w-auto">
                    @php $total = 0 @endphp
                        @forelse ($r_list->payment as $item)
                            @php $total += (double)$item->amount @endphp
                            @if($item->payment_method === $r_list->payment_method)
                                <div class="flex flex-col-reverse md:flex-row  justify-center items-center w-full h-full space-y-2 md:space-x-5">
                                    <div class="w-96 rounded my-5">
                                        @if(!isset($item->approval))
                                            <div class="mb-3 text-2xl md:text-4xl text-blue-500 flex items-center space-x-3">
                                                <span><i class="fa-solid fa-spinner"></i></span>
                                                <span class="text-sm md:text-xl font-bold">Pending</span>
                                            </div>
                                        @endif
                                        @if($item->approval === 1)
                                            <div class="mb-3 text-2xl md:text-4xl text-primary flex items-center space-x-3">
                                                <span><i class="fa-regular fa-face-smile"></i></span>
                                                <span class="text-sm md:text-xl font-bold">Approved</span>
                                            </div>
                                        @endif
                                        @if($item->approval === 0)
                                        <div class="mb-3 text-2xl md:text-4xl text-error flex items-center space-x-3">
                                            <span><i class="fa-regular fa-face-frown"></i></i></span>
                                                <span class="text-sm md:text-xl font-bold">Disapproved</span>
                                            </div>
                                        @endif
                                        <p class="text-neutral font-bold text-lg md:text-xl">{{$r_list->payment_method}} Payment from {{Carbon\Carbon::createFromFormat('Y-m-d h:i:s', $item->created_at)->format('F j, Y')}}</p>
                                        <div class="mt-5">
                                            <p class="text-neutral text-sm md:text-xl"><strong>Payer Name: </strong>{{$item->payment_name}}</p>
                                            <p class="text-neutral text-sm md:text-xl"><strong>Reference No.: </strong>{{$item->reference_no}}</p>
                                            <p class="text-neutral text-sm md:text-xl"><strong>Amount: </strong>{{$item->amount}}</p>
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
                            <h1 class="font-bold text-xl">No Payment Sent</h1>
                        </div>

                        @endforelse
                </article>
            </div>
        </section>
    </x-full-content>
</x-landing-layout>