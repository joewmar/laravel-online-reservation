@props(['rlist', 'noPic' => false])
<div class="w-full sm:flex sm:space-x-6">
    @if(!$noPic)
        <div class="flex-shrink-0 mb-6 h-32 sm:h-32 w-32 sm:w-32 sm:mb-0">
            @if(filter_var($rlist->userReservation->avatar ?? '', FILTER_VALIDATE_URL))
                <img src="{{$rlist->userReservation->avatar}}" alt="" class="object-cover object-center w-full h-full rounded">
            @elseif($rlist->userReservation->avatar ?? false)
                <img src="{{asset('storage/'. $rlist->userReservation->avatar)}}" alt="" class="object-cover object-center w-full h-full rounded">
            @else
                <img src="{{asset('images/avatars/no-avatar.png')}}" alt="" class="object-cover object-center w-full h-full rounded">
            @endif
        </div>  
    @endif
    <div class="flex flex-col space-y-4">
        <div>
            <h2 class="text-2xl font-semibold">{{$rlist->userReservation->name() ?? 'None'}}</h2>
            <span class="block text-sm text-neutral">{{($rlist->userReservation->age() . ' years old') ?? 'None'}} from {{$rlist->userReservation->country ?? 'None'}}</span>
            <span class="text-sm text-neutral">{{$rlist->userReservation->nationality ?? 'None'}}</span>
        </div>
        <div class="space-y-1">
            <span class="flex items-center space-x-2">
                <i class="fa-regular fa-envelope w-4 h-4"></i>
                <span class="text-neutral">{{$rlist->userReservation->email ?? 'None'}}</span>
            </span>
            <span class="flex items-center space-x-2">
                <i class="fa-solid fa-phone w-4 h-4"></i>
                <span class="text-neutral">{{$rlist->userReservation->contact ?? 'None'}}</span>
            </span>
        </div>
    </div>
</div>
