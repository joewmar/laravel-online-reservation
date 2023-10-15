<x-landing-layout noFooter>
    <x-full-content>
        <div class="flex items-center justify-center w-full h-screen bg-primary">
            <div class="modal-box">
                <form x-data="{ countdown: 15}" action="{{route('profile.update.user.info.email.verified', encrypt($user->id))}}" method="POST">
                    @csrf
                    @method('PUT')
                    <h3 class="font-bold text-lg">Before to save, let's verify your Email: {{$email}}</h3>
                    <div class="w-full text-center my-5">
                        <div id="passform" class="flex justify-center space-x-10">
                            <input class="passcode-input w-16 h-16 input input-bordered input-primary" type="text" maxlength=1 />
                            <input class="passcode-input w-16 h-16 input input-bordered input-primary" type="text" maxlength=1 />
                            <input class="passcode-input w-16 h-16 input input-bordered input-primary" type="text" maxlength=1 />
                            <input class="passcode-input w-16 h-16 input input-bordered input-primary" type="text" maxlength=1 />
                            <input id="passcode" name="code" type="hidden" />
                        </div>
                    </div>
                    <template x-if="!(countdown <= 0)">
                        <div>Resend after: <span x-text="countdown" class="text-lg"></span> sec</div>
                    </template>
                    <div x-init="setInterval (() => { countdown -= 1; if(countdown <= 0) countdown = 0}, 1000)" class="modal-action">
                        <a href="{{route('profile.update.user.info.email.resend', encrypt($user->id))}}" class="btn btn-ghost" :disabled="countdown != 0">Resend</a>
                        <button class="btn btn-primary">Verify</button>
                    </div>
                </form>
            </div>
        </div>
    </x-full-content>
    @push('scripts')
        <script src="{{Vite::asset("resources/js/passcode.js")}}"></script>
    @endpush
</x-landing-layout>

