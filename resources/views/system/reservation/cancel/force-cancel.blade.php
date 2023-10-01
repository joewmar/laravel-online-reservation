
<x-system-layout :activeSb="$activeSb">
    <x-system-content title="" back=true>
        {{-- User Details --}}
       <div class="px-0 md:px-20">
        <div class="w-full sm:flex sm:space-x-6">
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
        <div class="divider"></div>
        <article x-data="{reason: '{{old('reason')}}'}" class="text-md tracking-tight text-neutral my-5 w-auto">
            <form id="force-cancel-form" action="" method="post">
                @csrf
                @method('PUT')
                {{-- route('system.reservation.force.cancel.update', encrypt($r_list->id) --}}
            <h2 class="text-2xl mb-5 font-bold">Reason To Cancel</h2>
            <div class="form-control w-full">
                <label for="cancel" class="w-full relative flex justify-start rounded-md border border-gray-400 shadow-sm focus-within:border-primary focus-within:ring-1 focus-within:ring-primary ">
                    <select x-model="reason" name="reason" id="reason" class='w-full select select-primary peer border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0'>
                        <option value="" disabled selected>Please select</option>
                        <option value="No Room Available">No Room Available</option>
                        <option value="Unable to pay the downpayment">Unable to pay the downpayment</option>
                        <option value="Did not appear at the guesthouse.">Did not appear at the guesthouse.</option>
                        <option value="Other">Other</option>
                    </select>        
                    <span id="cancel" class="pointer-events-none absolute start-2.5 top-0 -translate-y-1/2 bg-white p-0.5 text-xs text-neutral transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-0 peer-focus:text-xs">
                        Reason To Cancel
                    </span>
                </label>
                <label class="label">
                    <span class="label-text-alt">
                        @error('reason')
                            <span class="label-text-alt text-error">{{$message}}</span>
                        @enderror
                    </span>
                </label>
            </div>
            <div x-show="reason == 'Other' " class="my-5">
                <span class="text-xl font-medium ">Other</span>
                <div class="mt-3">
                    <x-textarea name="message" id="message" placeholder="Reason Message" />
                </div>
            </div> 
            <x-passcode-modal title="Force Cancel Confirmation" id="disaprove" formId="force-cancel-form" />        
        </form>   
        </article>
        <div class="flex justify-end space-x-1">
            <label for="disaprove" class="btn btn-error btn-sm">Force Cancel</label>
        </div>
       </div>
    </x-system-content>
</x-system-layout>