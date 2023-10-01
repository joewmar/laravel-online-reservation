
<x-landing-layout noFooter>
    <x-navbar :activeNav="$activeNav" type="plain"/>
    <x-full-content>
        <section class="px-10 md:px-20 pt-24">
            {{-- User Details --}}
            <a href="{{URL::previous()}}" class="btn btn-ghost btn-circle">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="px-0 md:px-20">

                <div class="w-full sm:flex sm:space-x-6">
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
                <div class="divider"></div>
                <article class="text-md tracking-tight text-neutral my-5 w-auto">
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
                                                <span class="text-xl font-bold">Disapprove</span>
                                            </div>
                                        @endif
                                        <p class="text-neutral font-bold text-2xl">{{$r_list->payment_method}} Payment from {{Carbon\Carbon::createFromFormat('Y-m-d h:i:s', $item->created_at)->format('F j, Y')}}</p>
                                        <div class="mt-5">
                                            <p class="text-neutral text-lg"><strong>Payer Name: </strong>{{$item->payment_name}}</p>
                                            <p class="text-neutral text-lg"><strong>Reference No.: </strong>{{$item->reference_no}}</p>
                                            <p class="text-neutral text-lg"><strong>Amount: </strong>{{$item->amount}}</p>
                                        </div>
                                    </div>
                                    <div class="w-72 rounded">
                                        <img src="{{route('private.image', ['folder' => explode('/', $item->image)[0], 'filename' => explode('/', $item->image)[1]])}}" alt="Payment Receipt of {{$r_list->userReservation->name()}}">
                                    </div>
                                </div>
                                <div class="divider md:hidden"></div>
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