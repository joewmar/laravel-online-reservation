@php
    $arrType = ['Admin','Manager','Front Desk','Staff'];
    $arrKeyType = array_keys($arrType);

@endphp
<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Edit {{$employee->first_name}} {{$employee->last_name}}">
        <section class="pt-24 p-6 text-neutral">
            <form action="{{route('system.setting.accounts.update', encrypt($employee->id))}}" method="POST" class="flex justify-center" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="md:w-96">
                    <x-select name="type" id="type" placeholder="Role" :value="$arrKeyType" :title="$arrType" selected="{{$arrType[$employee->type] ?? ''}}" />
                    <x-input type="text" name="first_name" id="first_name" placeholder="First Name" value="{{$employee->first_name ?? ''}}" />
                    <x-input type="text" name="last_name" id="last_name" placeholder="Last Name" value="{{$employee->last_name ?? ''}}"/>
                    <x-input type="text" type="number" name="contact" id="contact" placeholder="Contact" value="{{$employee->contact ?? ''}}" />
                    <x-input type="email" name="email" id="email" placeholder="Email Address" value="{{$employee->email ?? ''}}" />
                    <x-input type="text" name="username" id="username" placeholder="Username" value="{{$employee->username ?? ''}}" />
                    <x-input type="password" name="password" id="password" placeholder="Password"  />
                    <x-input type="number" name="passcode" id="passcode" placeholder="Passcode" />
                    <x-input type="text" name="telegram_username" id="telegram_username" placeholder="Telegram Username" />
                    <p class="text-red-500 my-5">Note: To be valid and obtain a chat ID for sending notifications, simply search for <kbd class="kbd">{{ \Telegram\Bot\Laravel\Facades\Telegram::getMe()->getUsername()}}</kbd> and <kbd class="kbd">{{\Telegram\Bot\Laravel\Facades\Telegram::bot('bot2')->getMe()->getUsername()}}</kbd> (for Employee Notification) on telegram app. Then, type anything and send it. If you receive a message from Telegram, it means that the Telegram username is valid.</p>
                    <input type="file" name="avatar" class="file-input file-input-bordered file-input-primary w-full" />
                    @error('avatar')
                        <span class="mb-5 label-text-alt text-error">{{$message}}</span>
                    @enderror
                    <button class="btn btn-primary w-full mt-5">Save</button>
                </div>
            </form>
        </section>
    </x-system-content>
</x-system-layout>