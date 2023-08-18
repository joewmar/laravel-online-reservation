<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Change Access Code">
        @php

        @endphp
        <div x-data="{tab: 'pswd'}" class="py-10 block md:flex flex-col items-center">
            <div class="tabs tabs-boxed bg-transparent mb-3">
                <a @click="tab = 'pswd'" :class="tab === 'pswd' ? 'tab tab-active' : 'tab' ">Password</a> 
                <a @click="tab = 'pscd'" :class="tab === 'pscd' ? 'tab tab-active' : 'tab' ">Passcode</a> 
            </div>
            <template x-if="tab == 'pswd'">
                <div class="card w-full md:w-4/6 bg-base-100 shadow-2xl my-3">
                    <form id="update_form" action="{{route('system.profile.password.update', encrypt(auth('system')->user()->id))}}" method="post">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <h2 class="card-title">Change Password</h2>
                            <p>
                                <x-input type="password" name="current_password" id="current_password" placeholder="Current Password" />
                                <x-input type="password" name="new_password" id="new_password" placeholder="New Password" />
                                <x-input type="password" name="new_password_confirmation" id="new_password_confirmation" placeholder="Confirm New Password" />
                            </p>
                            <div class="card-actions justify-end">
                                <label for="update_modal" class="btn btn-primary">Change</label>
                                <x-modal id="update_modal" formID="update_form" title="Do you want to change password?" type="YesNo" loader>
                                </x-modal >
                            </div>
                        </div>
                    </form>
                </div>
            </template>
            <template x-if="tab == 'pscd'">
                <div class="card w-full md:w-4/6 bg-base-100 shadow-2xl my-3">
                    <form id="update_passcode_form" action="{{route('system.profile.passcode.update', encrypt(auth('system')->user()->id))}}" method="post">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <h2 class="card-title">Change Passcode</h2>
                            <p>
                                @if($errors->any())
                                    {!! implode('', $errors->all('<div>:message</div>')) !!}
                                @endif
                                <x-input type="password" name="current_passcode" id="current_passcode" placeholder="Current Passcode" maxlength="4" />
                                <x-input type="password" name="new_passcode" id="new_passcode" placeholder="New Passcode" maxlength="4" />
                                <x-input type="password" name="confirm_new_passcode" id="confirm_new_passcode" placeholder="Confirm New Passcode" maxlength="4" />
                            </p>
                            <div class="card-actions justify-end">
                                <label for="update_passcode" class="btn btn-primary">Change</label>
                                <x-modal id="update_passcode" formID="update_passcode_form" title="Do you want to change passcode?" type="YesNo" loader>
                                </x-modal >
                            </div>
                        </div>
                    </form>
                </div>
            </template>
        </div>
    </x-system-content >        
</x-system-layout >
