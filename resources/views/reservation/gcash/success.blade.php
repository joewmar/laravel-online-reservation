<x-landing-layout>
    @push('styles')
        <style>
            swiper-container {
            width: 320px;
            height: 240px;
            }
        
            swiper-slide {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            font-weight: bold;
            color: #fff;
            }
        
            
        </style>
    @endpush
    <x-full-content>
            <div class="flex flex-col-reverse md:flex-row justify-center items-center w-full h-full space-y-2 md:space-x-3">
                <div  class="w-96 rounded">
                    <p class="text-neutral text-2xl">Your Payment still verify, Please wait send a email notification for approval</p>
                    <div class="my-5">
                        <swiper-container init="false" class="mySwiper">
                            @forelse ($contacts as $contact)
                                <swiper-slide class="shadow-lg rounded-md bg-base-100 border border-neutral text-neutral">
                                    <div class="w-full h-full p-6">
                                        <h1>{{$contact['name']}}</h1>
                                        <div class="flex space-x-3">
                                            <p class="text-sm font-semibold"> Contact Number:</p>
                                            <ul class="text-sm font-normal">
                                                @foreach ($contact['contactno'] as $contactno)
                                                    <li>{{$contactno}}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="flex space-x-3">
                                            <p class="text-sm font-semibold"> Contact Email:</p>
                                            <ul class="text-sm font-normal">
                                                @foreach ($contact['email'] as $email)
                                                    <li>{{$email}}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="flex space-x-3">
                                            <p class="text-sm font-semibold">Facebook:</p>
                                            <ul class="text-sm font-normal">
                                                @foreach ($contact['fbuser'] as $fbuser)
                                                    <li>{{$fbuser}}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="flex space-x-3">
                                            <p class="text-sm font-semibold">WhatsApp:</p>
                                            <ul class="text-sm font-normal">
                                                @foreach ($contact['whatsapp'] as $whatsapp)
                                                    <li>{{$whatsapp}}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </swiper-slide>
                            @empty
                                <swiper-slide class="bg-primary shadow-md">No Contact Information</swiper-slide>
    
                            @endforelse
                            {{-- <swiper-slide>Slide 7</swiper-slide>
                            <swiper-slide>Slide 8</swiper-slide>
                            <swiper-slide>Slide 9</swiper-slide> --}}
                        </swiper-container>
                    </div>
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
            @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-element-bundle.min.js"></script>
            <script>
                var swiperEl = document.querySelector(".mySwiper");
                Object.assign(swiperEl, {
                  grabCursor: true,
                  effect: "creative",
                  creativeEffect: {
                    prev: {
                      shadow: true,
                      translate: [0, 0, -400],
                    },
                    next: {
                      translate: ["100%", 0, 0],
                    },
                  },
                });
                swiperEl.initialize()
            
                var swiper2El = document.querySelector(".mySwiper2");
                Object.assign(swiper2El, {
                  grabCursor: true,
                  effect: "creative",
                  creativeEffect: {
                    prev: {
                      shadow: true,
                      translate: ["-120%", 0, -500],
                    },
                    next: {
                      shadow: true,
                      translate: ["120%", 0, -500],
                    },
                  },
                });
                swiper2El.initialize()
            
                var swiper3El = document.querySelector(".mySwiper3");
                Object.assign(swiper3El, {
                  grabCursor: true,
                  effect: "creative",
                  creativeEffect: {
                    prev: {
                      shadow: true,
                      translate: ["-20%", 0, -1],
                    },
                    next: {
                      translate: ["100%", 0, 0],
                    },
                  },
                });
                swiper3El.initialize()
            
                var swiper4El = document.querySelector(".mySwiper4");
                Object.assign(swiper4El, {
                  grabCursor: true,
                  effect: "creative",
                  creativeEffect: {
                    prev: {
                      shadow: true,
                      translate: [0, 0, -800],
                      rotate: [180, 0, 0],
                    },
                    next: {
                      shadow: true,
                      translate: [0, 0, -800],
                      rotate: [-180, 0, 0],
                    },
                  },
                });
                swiper4El.initialize()
            
                var swiper5El = document.querySelector(".mySwiper5");
                Object.assign(swiper5El, {
                  grabCursor: true,
                  effect: "creative",
                  creativeEffect: {
                    prev: {
                      shadow: true,
                      translate: ["-125%", 0, -800],
                      rotate: [0, 0, -90],
                    },
                    next: {
                      shadow: true,
                      translate: ["125%", 0, -800],
                      rotate: [0, 0, 90],
                    },
                  },
                });
                swiper5El.initialize()
            
                var swiper6El = document.querySelector(".mySwiper6");
                Object.assign(swiper6El, {
                  grabCursor: true,
                  effect: "creative",
                  creativeEffect: {
                    prev: {
                      shadow: true,
                      origin: "left center",
                      translate: ["-5%", 0, -200],
                      rotate: [0, 100, 0],
                    },
                    next: {
                      origin: "right center",
                      translate: ["5%", 0, -200],
                      rotate: [0, -100, 0],
                    },
                  },
                });
                swiper6El.initialize()
            </script>
            @endpush
    </x-full-content>
</x-landing-layout>
