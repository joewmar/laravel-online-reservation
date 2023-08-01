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
        <h1 class="font-bold text-xl">Payment: <span class="font-normal text-xl">{{$r_list->payment_method}}</span></h1>
        <div class="divider"></div>
        <div class="block">
            <article class="text-md tracking-tight text-neutral my-5 p-5 w-auto">
                    @forelse ($r_list->payment as $item)
                        @if($item->payment_method === $r_list->payment_method)
                            <div class="flex flex-col-reverse md:flex-row  justify-center items-center w-full h-full space-y-2 md:space-x-5">
                                <div class="w-96 rounded">
                                    <p class="text-neutral font-bold text-2xl">Payment information</p>
                                    <div class="mt-5">
                                        <x-input type="text" name="payment_name" id="payment_name" placeholder="Payer Name" value="{{$item->payment_name}}" />
                                        <x-input type="text" name="reference_no" id="reference_no" placeholder="Reference No." value="{{$item->reference_no}}" />
                                        <x-input type="number" name="amount" id="amount" placeholder="Total Amount" value="{{$item->amount}}" />
                                    </div>
                                    <div class="flex space-x-1 mt-5">
                                        <label for="approve{{$item->id}}" class="btn btn-info btn-sm" >Approve</label>
                                        <label for="disaprove{{$item->id}}"class="btn btn-error btn-sm" >Disaprove</label>
                                        <x-modal id="approve{{$item->id}}" title="Approve for Payment Name: {{$item->payment_name}} ">
                                        </x-modal>
                                        <x-modal id="disaprove{{$item->id}}" title="Why disapprove of Payment Name: {{$item->payment_name}}?">
                                        </x-modal>
                                    </div>
                                </div>
                                <div class="avatar">
                                    <div class="w-96 rounded">
                                        <img src="{{asset('storage/'. $item->image)}}" class="show_img" />
                                    </div>
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