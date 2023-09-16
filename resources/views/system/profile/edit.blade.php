<x-system-layout :activeSb="$activeSb">
  <x-system-content back="{{route('system.profile.home')}}">
          <div class="hidden md:block divider"></div>
          <div class="w-full p-8 sm:flex sm:space-x-6">
            <div class="flex-shrink-0 mb-6 h-15 sm:h-32 w-15 sm:w-32 sm:mb-0">
                @if($systemUser->avatar)
                    <img src="{{route('private.image', ['folder' => explode('/', $systemUser->avatar)[0], 'filename' => explode('/', $systemUser->avatar)[1]])}}" alt="" class="object-cover object-center w-full h-full rounded">
                @else
                    <img src="{{asset('images/avatars/no-avatar.png')}}" alt="" class="object-cover object-center w-full h-full rounded">
                @endif
            </div>            
            <div class="flex flex-col space-y-4">
                <div>
                    <h2 class="text-2xl font-semibold">{{$systemUser->name()}}</h2>
                </div>
                <div class="space-y-1">
                    <span class="flex items-center space-x-2">
                        <i class="fa-regular fa-envelope w-4 h-4"></i>
                        <span class="text-neutral">{{$systemUser->email}}</span>
                    </span>
                    <span class="flex items-center space-x-2">
                        <i class="fa-solid fa-phone w-4 h-4"></i>
                        <span class="text-neutral">{{$systemUser->contact}}</span>
                    </span>

                </div>
                <span class="w-full">
                    <label for="avatar_modal" class="btn btn-secondary btn-sm">Change Avatar</label>
                    <x-modal id="avatar_modal" title="Choose Photo">
                        <form action="{{route('system.profile.update.avatar', encrypt($systemUser->id))}}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <x-drag-drop name="avatar" id="avatar1" />
                            <div class="modal-action">
                                <button class="btn btn-primary" type="submit">Save</button>
                            </div>
                        </form>
                    </x-modal>
                </span>
            </div>
          </div>
          <div class="divider"></div>
            <form method="POST" id="edit_form" action="{{route('system.profile.update', encrypt($systemUser->id))}}" class="container flex justify-center mx-auto space-y-12 ng-untouched ng-pristine ng-valid w-full" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="w-96">
                    <x-input type="text" name="first_name" id="first_name" placeholder="First Name" value="{{$systemUser->first_name ?? ''}}" />
                    <x-input type="text" name="last_name" id="last_name" placeholder="Last Name" value="{{$systemUser->last_name ?? ''}}"/>
                    <x-input type="text" type="number" name="contact" id="contact" placeholder="Contact" value="{{$systemUser->contact ?? ''}}" />
                    <x-input type="email" name="email" id="email" placeholder="Email Address" value="{{$systemUser->email ?? ''}}" />
                    <x-input type="text" name="username" id="username" placeholder="Username" value="{{$systemUser->username ?? ''}}" />
                    <x-input type="text" name="telegram_username" id="telegram_username" placeholder="Telegram Username" value="{{$systemUser->telegram_username}}" />
                    <p class="text-red-500 my-5">Note: To be valid for sending notifications, simply search for <kbd class="kbd">{{ \Telegram\Bot\Laravel\Facades\Telegram::getMe()->getUsername()}}</kbd> and <kbd class="kbd">{{\Telegram\Bot\Laravel\Facades\Telegram::bot('bot2')->getMe()->getUsername()}}</kbd> (for systemUser Notification) on telegram app. Then, type anything and send it. If you receive a message from Telegram, it means that the Telegram username is valid.</p>
                    <label for="change_modal" class="btn btn-primary w-full mt-5">Save</label>
                    <x-modal id="change_modal" title="Do you want change information" type="YesNo" loader=true formID="edit_form">
                    </x-modal >
                </div>
            </form>
  </x-system-content>
</x-system-layout>
