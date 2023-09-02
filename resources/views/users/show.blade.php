<x-landing-layout noFooter>
    <x-navbar :activeNav="$activeNav" type="plain"/>
    <x-full-content>
        <section class="pt-24 p-6 text-neutral h-auto  ">
            <div class="container flex flex-col mx-auto space-y-12 ng-untouched ng-pristine ng-valid">
                <fieldset class="grid grid-cols-1 md:grid-cols-6 gap-10 md:gap-2 p-6 rounded-md shadow-sm">
                    <div class="col-span-2 space-y-5">
                        <div class="flex-shrink-0 mb-6 h-15 sm:h-32 w-15 sm:w-32 sm:mb-0">
                            @if(filter_var($user->avatar ?? '', FILTER_VALIDATE_URL))
                                <img src="{{$user->avatar}}" alt="{{$user->name()}} Profile Pic" class="object-cover object-center w-full h-full rounded">
                            @elseif($user->avatar ?? false)
                                <img src="{{asset('storage/'. $user->avatar)}}" alt="{{$user->name()}} Profile Pic" class="object-cover object-center w-full h-full rounded">
                            @else
                                <img src="{{asset('images/avatars/no-avatar.png')}}" alt="{{$user->name()}} Profile Pic" class="object-cover object-center w-full h-full rounded">
                            @endif
                        </div> 
                        <label for="profike_modal" class="btn btn-secondary btn-sm">Change Profile Pic</label> 
                        <x-modal id="profike_modal" title="Change Profile Pic">
                            <form action="{{route('profile.update.avatar', encrypt($user->id))}}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <x-drag-drop name="avatar" id="avatar" />
                                <div class="modal-action">
                                    <button class="btn btn-secondary">Change</button>
                                </div>
                            </form>
                        </x-modal>
                    </div>
                    <div class="col-span-4 w-full">
                        <form id="user_info_form" action="{{route('profile.update.user.info', encrypt($user->id))}}" method="POST">
                            @csrf
                            @method('PUT')
                            <h2 class="text-2xl font-bold mb-5">Personal Information</h2>
                            <div class="my-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-input name="first_name" id="first_name" placeholder="First Name" value="{{$user->first_name}}" />
                                <x-input name="last_name" id="last_name" placeholder="First Name" value="{{$user->last_name}}" />
                                <x-datetime-picker name="birthday" id="birthday" placeholder="Brithday" class="flatpickr-reservation-one" value="{{$user->birthday}}" />
                                <x-datalist-input name="nationality" id="nationality" placeholder="Nationality" :lists="$nationality" value="{{$user->nationality}}" />
                                <x-datalist-input name="country" id="country" placeholder="Country" :lists="$countries" value="{{$user->country}}" />
                                <x-phone-input value="{{$user->contact}}"/>
                                <x-input type="email" name="email" id="email" placeholder="Contact Email" value="{{$user->email}}" />
                            </div>
                            <label for="user_info_modal" class="btn btn-primary btn-sm">Save</label> 
                            <x-modal id="user_info_modal" title="Do you want to save your information?" type="YesNo" formID="user_info_form" loader>
                            </x-modal> 
                        </form>
                        <div class="divider"></div>
                        <form action="{{route('profile.update.password', encrypt($user->id))}}" method="POST">
                            @csrf
                            @method('PUT')
                            <h2 class="text-2xl font-bold mb-5">Password</h2>
                            <div x-data="{loader: false}" class="my-5 w-96">
                                <x-loader />
                                <x-password name="current_password" id="current_password" placeholder="Current Password" />
                                <x-password name="new_password" id="new_password" placeholder="New Password" validation/>
                                <x-input type="password" name="new_password_confirmation" id="new_password_confirmation" placeholder="Confirm New Password" />
                                <button @click="loader = true" class="btn btn-warning btn-sm">Change</button>  
                            </div>

                        </form>
                        <div class="divider"></div>
                            <h2 class="text-2xl font-bold mb-5">Valid ID</h2>
                            @if(isset($user->valid_id))
                                <div class="w-96 rounded">
                                    <img src="{{route('private.image', ['folder' => explode('/', $user->valid_id)[0], 'filename' => explode('/', $user->valid_id)[1]])}}" alt="Valid ID of {{$user->name()}}">
                                </div>
                            @else
                                <h3 class="text-2xl font-bold mb-5 text-center">No Valid ID</h3>
                            @endif
                            <label for="edit_id_modal" class="btn btn-primary btn-sm mt-5">Change</label> 
                            <x-modal id="edit_id_modal" title="Change Valid ID" loader>
                                <form action="{{route('profile.update.validid', encrypt($user->id))}}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <x-drag-drop name="valid_id" id="valid_id" />
                                    <div class="modal-action">
                                        <button type="submit" class="btn btn-primary" @click="loader = true">Save</button>
                                    </div>
                                </form>
                            </x-modal>
                    </div>
                </fieldset>
            </div>
        </section>
    </x-full-content>
</x-landing-layout>
