<x-landing-layout noFooter>
    <x-navbar :activeNav="$activeNav" type="plain"/>
    <x-full-content>
        <section class="pt-24 text-neutral h-auto mb-3">
            <div class="container flex flex-col mx-auto space-y-12 ng-untouched ng-pristine ng-valid">
                <fieldset class="w-full grid grid-cols-1 md:grid-cols-6 gap-10 md:gap-2 p-6 rounded-md">
                    <div class="col-span-6 md:col-span-2 space-y-5 flex flex-col items-center">
                        <div class="flex-shrink-0 h-52 sm:h-32 w-52 sm:w-32 sm:mb-0">
                            @if(filter_var($user->avatar ?? '', FILTER_VALIDATE_URL))
                                <img src="{{$user->avatar}}" alt="{{$user->name()}} Profile Pic" class="object-cover object-center w-full h-full rounded">
                            @elseif($user->avatar ?? false)
                                <img src="{{asset('storage/'. $user->avatar)}}" alt="{{$user->name()}} Profile Pic" class="object-cover object-center w-full h-full rounded">
                            @else
                                <img src="{{asset('images/avatars/no-avatar.png')}}" alt="{{$user->name()}} Profile Pic" class="object-cover object-center w-full h-full rounded">
                            @endif
                        </div> 
                        <div>
                            <label for="profike_modal" class="btn btn-secondary btn-sm btn-block">Change Image</label> 
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
                    </div>
                    <div class="col-span-6 md:col-span-4 w-full">
                        <form id="user_info_form" action="{{route('profile.update.user.info', encrypt($user->id))}}" method="POST">
                            @csrf
                            @method('PUT')
                            <h2 class="text-2xl font-bold mb-5">Personal Information</h2>
                            <div class="my-5 grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-4">
                                <x-input name="first_name" id="first_name" placeholder="First Name" value="{{$user->first_name}}"  noRequired/>
                                <x-input name="last_name" id="last_name" placeholder="First Name" value="{{$user->last_name}}"  noRequired/>
                                <x-birthday-input value="{{$user->birthday}}"  noRequired/>
                                <x-datalist-input name="nationality" id="nationality" placeholder="Nationality" :lists="$nationality" value="{{$user->nationality}}"  noRequired/>
                                <x-datalist-input name="country" id="country" placeholder="Country" :lists="$countries" value="{{$user->country}}"  noRequired/>
                                <x-phone-input value="{{$user->contact}}" noRequired/>
                                <x-input type="email" name="email" id="email" placeholder="Contact Email" value="{{$user->email}}"  noRequired/>
                            </div>
                            @if(!$isPending)
                                <label for="user_info_modal" class="btn btn-primary btn-sm">Save</label> 
                                <x-modal id="user_info_modal" title="Do you want to save your information?" type="YesNo" formID="user_info_form" loader>
                                </x-modal> 
                            @else
                                <p class="text-xs text-red-500 mb-5">* You cannot change your personal information that you still have some pending reservation</p>
                                <label class="btn btn-primary btn-sm" disabled>Save</label> 
                            @endif
                        </form>
                        <div class="divider"></div>
                        <form id="passf" action="{{route('profile.update.password', encrypt($user->id))}}" method="POST">
                            @csrf
                            @method('PUT')
                            <h2 class="text-2xl font-bold mb-5">Change Password</h2>
                            <div x-data="{loader: false}" class="my-5 w-full md:w-96">
                                <x-loader />
                                <x-password name="current_password" id="current_password" placeholder="Current Password" />
                                <x-password name="new_password" id="new_password" placeholder="New Password" validation/>
                                <x-input type="password" name="new_password_confirmation" id="new_password_confirmation" placeholder="Confirm New Password" />
                                <label for="passmdl" class="btn btn-warning btn-sm">Change</label>  
                                <x-modal id="passmdl" title="Do you want to change your password?" type="YesNo" formID="passf" loader>
                                </x-modal> 
                            </div>

                        </form>
                        <div class="divider"></div>
                        <h2 class="text-2xl font-bold {{!$isPending ? 'mb-5' : ''}}">Valid ID</h2>
                        @if ($isPending)
                            <p class="text-xs text-red-500 {{$isPending ? 'mb-5' : ''}}">* You cannot change your valid ID because you still have pending reservation information.</p>
                        @endif

                        <div class="join mt-5">
                            <label for="show_id_modal" class="btn btn-sm join-item">Show</label> 
                            @if(!$isPending)
                                <label for="edit_id_modal'" class="btn btn-primary btn-sm join-item">Change</label> 
                                <x-modal id="edit_id_modal" title="Change Valid ID" loader>
                                    <form action="{{route('profile.update.validid', encrypt($user->id))}}" method="POST" enctype="multipart/form-data" >
                                        @csrf
                                        @method('PUT')
                                        <x-drag-drop name="valid_id" id="valid_id" />
                                        <div class="modal-action">
                                            <button type="submit" class="btn btn-primary" @click="loader = true">Save</button>
                                        </div>
                                    </form>
                                </x-modal>
                            @else
                                <label class="btn btn-primary btn-sm join-item" disabled>Change</label> 
                            @endif
                        </div>

                        <x-modal id="show_id_modal" title="Valid ID" loader>
                            @if(isset($user->valid_id))
                                <div class="w-full rounded">
                                    <img src="{{route('private.image', ['folder' => explode('/', $user->valid_id)[0], 'filename' => explode('/', $user->valid_id)[1]])}}" alt="Valid ID of {{$user->name()}}">
                                </div>
                            @else
                                <h3 class="text-2xl font-bold mb-5 text-center">No Valid ID</h3>
                            @endif
                        </x-modal>
                        <div class="divider"></div>
                        <h2 class="text-2xl font-bold {{!$canDelAcc ? 'mb-5' : ''}}">Account</h2>
                        @if ($canDelAcc)
                            <p class="text-xs text-red-500 {{$canDelAcc ? 'mb-5' : ''}}">* You cannot delete your account that you still have some pending reservation</p>
                            <label class="btn btn-primary btn-sm" disabled>Delete Account</label> 
                        @else
                            <label for="delete_acc_modal" class="btn btn-error btn-sm">Delete Account</label> 
                            <x-modal id="delete_acc_modal" title="Enter your password to confirm delete your account" loader>
                                <form action="{{route('profile.destroy.account', encrypt($user->id))}}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <p class="mb-3 text-error">When you delete your account, the previous reservation information will also be deleted.</p>
                                    <x-password name="dltpass" />
                                    <div class="modal-action">
                                        <button class="btn btn-error">Delete Account</button>
                                    </div>
                                </form>
                            </x-modal>
                        @endif
                    </div>
                </fieldset>
            </div>
        </section>
    </x-full-content>
</x-landing-layout>
