@php
    $arrType = ['Admin','Manager','Front Desk','Staff'];
    $arrKeyType = array_keys($arrType);

@endphp

<x-system-layout :activeSb="$activeSb">
    <x-system-content title="Create Account for Employee">
        <section class="pt-24 p-6 text-neutral">
            <form action="" method="POST" class="flex justify-center">
                @csrf
                <div class="md:w-96">
                    <x-select name="type" id="type" placeholder="Role" :value="$arrKeyType" :title="$arrType" selected="{{$arrType[old('type')] ?? ''}}" />
                    <x-input type="text" name="first_name" id="first_name" placeholder="First Name" />
                    <x-input type="text" name="last_name" id="last_name" placeholder="Last Name" />
                    <x-input type="text" type="number" name="contact" id="contact" placeholder="Contact" />
                    <x-input type="email" name="email" id="email" placeholder="Email Address" />
                    <x-input type="text" name="username" id="username" placeholder="Username" />
                    <x-input type="password" name="password" id="password" placeholder="Password" />
                    <x-input type="number" name="passcode" id="passcode" placeholder="Passcode" />
                    <x-input type="text" name="telegram_username" id="telegram_username" placeholder="Telegram Username" />
                    <p class="text-red-500 my-5">Note: To be valid and obtain a chat ID for sending notifications, simply search for "<strong>{{ \Telegram\Bot\Laravel\Facades\Telegram::getMe()->getUsername()}}</strong>" and "<strong>{{\Telegram\Bot\Laravel\Facades\Telegram::bot('bot2')->getMe()->getUsername()}}</strong>" (for Employee Notification) on telegram app. Then, type anything and send it. If you receive a message from Telegram, it means that the Telegram username is valid.</p>
                    <input type="file" name="avatar" class="file-input file-input-bordered file-input-primary w-full" />
                    <button class="btn btn-primary w-full mt-5">Create</button>
                </div>
            </form>
        </section>
    </x-system-content>
</x-system-layout>