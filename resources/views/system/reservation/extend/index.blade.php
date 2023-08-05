<x-system-layout :activeSb="$activeSb">
    <x-system-content title="" back=true>
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
        <div class="divider"></div>
        <form id="extend-form" action="" method="POST" class="w-full">
            @csrf
            @method('PUT')
            <article class="w-full flex justify-center">
                <div class="w-96">
                    <h2 class="text-2xl mb-5 font-bold">Extend days</h2>
                    <x-input name="no_days" id="no_days" placeholder="How many days to extend" value="{{old('no_days') ?? ''}}" /> 
                    <x-passcode-modal title="Extend Confirmation" id="extend_modal" formId="extend-form" />
                    <label for="extend_modal" class="btn btn-primary btn-block">Extend</label for="extend_modal">
                </div>
            </article>
        </form>
    </x-system-content>
</x-system-layout>