<x-landing-layout>
    <x-full-content>
            <div class="flex flex-col-reverse md:flex-row justify-center items-center w-full h-full space-y-2 md:space-x-3">
                <div  class="w-96 rounded">
                    <p class="text-neutral text-2xl">Your Payment still verify, Please wait send a email notification for approval</p>
                    <ul class="text-neutral text-lg mt-5">
                        <li class="pb-5"><span class="font-bold">If anything problem of payment, Please Contact on below</span></li>
                        <li><span class="font-bold">Contact No. </span>09123456789</li>
                        <li><span class="font-bold">WhatApp. </span>09123456789</li>
                        <li>
                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=bognothomestay@gmail.com" class="link link-hover">
                                <span class="font-bold">Email: </span>delacruz@email.com
                            </a>
                        </li>
                        <li>
                            <a href="https://www.facebook.com/100057519735244" class="link link-hover">
                                <span class="font-bold">Facebook Page: </span>Alvin and Angie Bognot mt Pinatubo Guesthouse and Tours
                            </a>
                        </li>
                    </ul>
                    <div class="flex justify-end">
                        <a href="{{route('home')}}" class="btn btn-primary">Go Home</a>
                    </div>
                </div>
                <div class="mockup-phone">
                    <div class="camera"></div> 
                    <div class="display">
                    <div class="artboard artboard-demo phone-1"> 
                        <img src="{{asset('images/payment/gcash-logo.png')}}" />
                    </div>
                    </div>
                </div>
            </div>
    </x-full-content>
</x-landing-layout>
