<x-landing-layout noFooter>
    <x-full-content>
        <form action="{{route('reservation.payment.store', encrypt($reservation->id))}}" method="POST" enctype="multipart/form-data">
            @csrf
            <div x-data="{all: true, step1: false, step2: false, step3: false, step4: false, gcashName: '', refNo: '', amount: ''}" x-cloak class="w-full px-5">
                <div x-show="all" class="flex flex-col-reverse md:flex-row justify-center items-center w-full h-full space-y-2 md:space-x-3 py-10">
                    <div  class="w-96 rounded text-sm md:text-xl mt-5">
                        <p class="text-neutral"><span class="font-bold">Step 1: </span>Pay via QR Scanner</p>
                        <p class="text-neutral"><span class="font-bold">Step 2: </span>Send Screenshot of Receipt of Gcash</p>
                        <p class="text-neutral"><span class="font-bold">Step 3: </span>Fill up the information for verify your payment</p>
                        <div class="flex justify-start md:justify-end mt-5">
                            <button type="button" @click="all = false, step1 = true" class="btn btn-primary btn-sm md:btn:md">Proceed</button>
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
                <div x-show="step1" class="flex flex-col-reverse md:flex-row  justify-center items-center w-full h-full space-y-2 md:space-x-3 py-10">
                    <div  class="w-96 rounded text-sm md:text-xl mt-5">
                        <p class="text-neutral"><span class="font-bold">Step 1: </span>Pay via QR Scanner</p>
                        <ul class="text-neutral text-sm md:text-lg mt-5">
                            <li><span class="font-bold">Gcash Name: </span>{{$reference['name']  ?? 'None'}}</li>
                            <li><span class="font-bold">Gcash No: </span>{{$reference['number']  ?? 'None'}}</li>
                        </ul>
                        <div class="flex justify-start md:justify-end mt-5 space-x-1">
                            <button type="button" @click="all = true, step1 = false" class="btn btn-ghost btn-primary btn-sm md:btn:md">Back</button>
                            <button type="button" @click="step1 = false, step2 = true" class="btn btn-primary btn-sm md:btn:md">Go Step 2</button>
                        </div>
                    </div>
                    <div class="mockup-phone">
                        <div class="camera"></div> 
                        <div class="display">
                            <div class="artboard artboard-demo phone-1"> 
                                <img src="{{isset($reference['qrcode']) ? route('private.image', ['folder' => explode('/', $reference['qrcode'])[0], 'filename' => explode('/', $reference['qrcode'])[1]]) : asset('images/payment/gcash-logo.png')}}" class="h-full w-full object-center" />
                            </div>
                        </div>
                    </div>
                </div>
                <div x-show="step2" class="flex flex-col-reverse md:flex-row  justify-center items-center w-full h-full space-y-2 md:space-x-3 py-10">
                    <div class="w-96 rounded text-sm md:text-xl mt-5">
                        <p class="text-neutral"><span class="font-bold">Step 2: </span>Send Screenshot of Receipt of Gcash and Fill up the information for verify your payment</p>
                        <div class="form-control w-full mt-5">
                            <label class="label">
                            <span class="label-text text-neutral">Send Screenshot here</span>
                            </label>
                            <input type="file" id="image" name="image"  class="file-input file-input-bordered file-input-primary w-full"/>
                            <label class="label">
                                @error('image')
                                    <span class="label-text">{{$message}}</span>
                                @enderror
                                <span class="hidden md:inline label-text-alt">We have sample below here</span>
                            </label>
                        </div>
                        <div id="info">
                            <x-input xModel="amount" type="numeric" placeholder="Total Amount" name="amount" id="amount" />
                            <x-input xModel="refNo" placeholder="Reference No." name="reference_no" id="reference_no" />
                            <x-input xModel="gcashName" placeholder="Gcash Name" name="payment_name" id="payment_name" />
                        </div>
                        <div class="flex justify-start md:justify-end mt-5 space-x-1">
                            <button type="button" @click="step1 = true, step2 = false" class="btn btn-ghost btn-primary btn-sm md:btn:md">Back</button>
                            <button type="button" @click="step2 = false, step3 = true" class="btn btn-primary btn-sm md:btn:md">Go Step 3</button>
                        </div>
                        
                    </div>
                    <div class="mockup-phone">
                        <div class="camera"></div> 
                        <div class="display">
                            <div class="artboard artboard-demo phone-1"> 
                                <img src="{{asset('images/payment/sample-gcash-receipt.jpg')}}" class="show_img h-full w-full object-center" />
                            </div>
                        </div>
                    </div>
                </div>
                <div x-show="step3" class="flex flex-col-reverse md:flex-row  justify-center items-center w-full h-full space-y-2 md:space-x-3 py-10">
                    <div x-data="{loader: false}" class="w-96 rounded text-sm md:text-xl mt-5">
                        <p class="text-neutral text-2xl"><span class="font-bold">Step 4: </span>Confirm the information</p>
                        <div class="mt-5 text-sm md:text-lg">
                            <p class="text-neutral"><span class="font-bold">Gcash Name </span><span x-text="gcashName">None</p>
                            <p class="text-neutral"><span class="font-bold">Reference No.: </span><span x-text="refNo">None</p>
                            <p class="text-neutral"><span class="font-bold">Total Amount </span><span x-text="amount">None</p>
                        </div>
                        <div class="flex justify-start md:justify-end mt-5 space-x-1">
                            <button type="button" @click="step3 = false, step2 = true" class="btn btn-ghost btn-primary btn-sm md:btn:md">Back</button>
                            <button @click="loader = true" type="submit" class="btn btn-primary btn-sm md:btn:md">Send Now</button>
                        </div>
                        <x-loader />
                    </div>
                    <div class="mockup-phone">
                        <div class="camera"></div> 
                        <div class="display">
                            <div class="artboard artboard-demo phone-1"> 
                                <img src="{{asset('images/payment/gcash-logo.png')}}" class="show_img" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </x-full-content>
    @push('scripts')
        <script type="module" src="{{Vite::asset('resources/js/payment-image.js')}}"></script>
    @endpush
</x-landing-layout>
