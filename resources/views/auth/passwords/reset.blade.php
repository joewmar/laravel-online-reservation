<x-landing-layout noFooter>
    <x-full-content>
        <div x-data="{loader: false}" class="bg-primary">
            <div class="flex justify-center items-center h-screen">
                <x-loader />
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="card w-96 bg-base-100 shadow-xl">
                        <div class="card-body">
                            <h2 class="card-title mb-5">{{ __('Reset Password') }}</h2>
                            <x-input type="email" name="email" id="email" placeholder="Email" value="{{ $email ?? old('email') }}" />
                            <x-input type="password" name="password" id="password" placeholder="Password" />
                            <x-input type="password" name="password_confirmation" id="password-confirm" placeholder="Confirm Password" />
                            <div class="card-actions justify-end">
                                <button @click="loader = true" class="btn btn-primary">Reset Now</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </x-full-content>

</x-landing-layout>



