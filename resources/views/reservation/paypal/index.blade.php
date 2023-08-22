<x-landing-layout>
    <x-full-content>
        <form action="{{route('reservation.payment.store', encrypt($reservation->id))}}" method="POST" enctype="multipart/form-data">
            @csrf
            <div x-data="{all: true, step1: false, step2: false, step3: false, step4: false, step5: false, paypalName: '', refNo: '', amount: ''}" x-cloak>
                <div x-show="all" class="flex flex-col-reverse md:flex-row justify-center items-center w-full h-full space-y-2 md:space-x-3">
                    <div  class="w-96 rounded">
                        <p class="text-neutral text-2xl"><span class="font-bold">Step 1: </span>Enter your recipient's name, PayPal username, email, or mobile number.</p>
                        <p class="text-neutral text-2xl"><span class="font-bold">Step 2: </span>Enter the amount you want to send and choose a currency. You can even add a personalized note.</p>
                        <p class="text-neutral text-2xl"><span class="font-bold">Step 3: </span>Choose "Send". Your payment is on its way.</p>
                        <p class="text-neutral text-2xl"><span class="font-bold">Step 4: </span>Send Screenshot of Receipt of PayPal</p>
                        <p class="text-neutral text-2xl"><span class="font-bold">Step 5: </span>Fill up the information for verify your payment</p>
                        <div class="flex justify-end">
                            <button type="button" @click="all = false, step1 = true" class="btn btn-primary">Proceed</button>
                        </div>
                    </div>
                    <div class="mockup-phone">
                        <div class="camera"></div> 
                        <div class="display">
                        <div class="artboard artboard-demo phone-1"> 
                            <img src="{{asset('images/payment/paypal-logo.png')}}" />
                        </div>
                        </div>
                    </div>
                </div>
                <div x-show="step1" class="flex flex-col-reverse md:flex-row  justify-center items-center w-full h-full space-y-2 md:space-x-3">
                    <div  class="w-96 rounded">
                        <p class="text-neutral text-2xl mb-3"><span class="font-bold">Step 1: </span>Enter your recipient's name, PayPal username, email, or mobile number.</p>
                        <ul class="text-neutral text-lg mt-5">
                            <li><span class="font-bold">PayPal Name: </span>{{$reference['name']}}</li>
                            <li><span class="font-bold">PayPal Mobile No.: </span>{{$reference['number']}}</li>
                            <li><span class="font-bold">PayPal Email: </span>{{$reference['email']}}</li>
                            <li><span class="font-bold">PayPal Username: </span>{{$reference['username']}}</li>
                        </ul>
                        <div class="flex justify-end space-x-1">
                            <button type="button" @click="all = true, step1 = false" class="btn btn-ghost btn-primary">Back</button>
                            <button type="button" @click="step1 = false, step2 = true" class="btn btn-primary">Go Step 2</button>
                        </div>
                    </div>
                    <div class="mockup-phone">
                        <div class="camera"></div> 
                        <div class="display">
                            <div class="artboard artboard-demo phone-1"> 
                                <img src="{{isset($reference['image']) ? route('private.image', ['folder' => explode('/', $reference['image'])[0], 'filename' => explode('/', $reference['image'])[1]]) : asset('images/payment/paypal-logo.png')}}" />
                            </div>
                        </div>
                    </div>
                </div>
                <div x-show="step2" class="flex flex-col-reverse md:flex-row  justify-center items-center w-full h-full space-y-2 md:space-x-3">
                    <div  class="w-96 rounded">
                        <p class="text-neutral text-2xl mb-3"><span class="font-bold">Step 2: </span>Enter the amount you want to send and choose a currency</p>
                        <div class="flex justify-end space-x-1">
                            <button type="button" @click="step1 = true, step2 = false" class="btn btn-ghost btn-primary">Back</button>
                            <button type="button" @click="step2 = false, step3 = true" class="btn btn-primary">Go Step 3</button>
                        </div>
                    </div>
                    <div class="mockup-phone">
                        <div class="camera"></div> 
                        <div class="display">
                            <div class="artboard artboard-demo phone-1"> 
                                <img src="{{asset('images/payment/paypal-step2.png')}}" />
                            </div>
                        </div>
                    </div>
                </div>
                <div x-show="step3" class="flex flex-col-reverse md:flex-row  justify-center items-center w-full h-full space-y-2 md:space-x-3">
                    <div  class="w-96 rounded">
                        <p class="text-neutral text-2xl mb-3"><span class="font-bold">Step 3: </span>Choose "Send". Your payment is on its way.</p>
                        <div class="flex justify-end space-x-1">
                            <button type="button" @click="step2 = true, step3 = false" class="btn btn-ghost btn-primary">Back</button>
                            <button type="button" @click="step3 = false, step4 = true" class="btn btn-primary">Go Step 4</button>
                        </div>
                    </div>
                    <div class="mockup-phone">
                        <div class="camera"></div> 
                        <div class="display">
                            <div class="artboard artboard-demo phone-1"> 
                                <img src="{{asset('images/payment/paypal-step3.png')}}" />
                            </div>
                        </div>
                    </div>
                </div>
                <div x-show="step4" class="flex flex-col-reverse md:flex-row  justify-center items-center w-full h-full space-y-2 md:space-x-3">
                    <div class="w-96 rounded">
                        <p class="text-neutral text-2xl"><span class="font-bold">Step 2: </span>Send Screenshot of Receipt of Gcash and Fill up the information for verify your payment</p>
                        <div class="form-control w-full mt-5">
                            <label class="label">
                            <span class="label-text text-neutral">Send Screenshot of Receipt</span>
                            </label>
                            <input type="file" id="image" name="image"  class="file-input file-input-bordered file-input-primary w-full"/>
                            <label class="label">
                                @error('image')
                                    <span class="label-text">{{$message}}</span>
                                @enderror
                                <span class="label-text-alt">We have sample below here</span>
                            </label>
                        </div>
                        <div id="info">
                            <x-input xModel="amount" type="numeric" placeholder="Total Amount" name="amount" id="amount" />
                            <x-input xModel="refNo" placeholder="Transaction ID." name="reference_no" id="reference_no" />
                            <x-input xModel="paypalName" placeholder="PayPal Receipt Name" name="payment_name" id="payment_name" />
                        </div>
                        <div class="flex justify-end space-x-1">
                            <button type="button" @click="step3 = true, step4 = false" class="btn btn-ghost btn-primary">Back</button>
                            <button id="done" type="button" @click="step4 = false, step5 = true" class="btn btn-primary">Go Step 5</button>
                        </div>
                        
                    </div>
                    <div class="mockup-phone">
                        <div class="camera"></div> 
                        <div class="display">
                            <div class="artboard artboard-demo phone-1"> 
                                <img src="{{asset('images/payment/paypal-sample-receipt.jpg')}}" class="show_img" />
                            </div>
                        </div>
                    </div>
                </div>
                <div x-show="step5" class="flex flex-col-reverse md:flex-row  justify-center items-center w-full h-full space-y-2 md:space-x-3">
                    <div x-data="{loader: false}" class="w-96 rounded">
                        <p class="text-neutral text-2xl"><span class="font-bold">Step 4: </span>Confirm the information</p>
                        <div class="mt-5">
                            <p class="text-neutral text-lg"><span class="font-bold">PayPal Receipt Name </span><span x-text="paypalName">None</p>
                            <p class="text-neutral text-lg"><span class="font-bold">Transaction ID: </span><span x-text="refNo">None</p>
                            <p class="text-neutral text-lg"><span class="font-bold">Total Amount </span><span x-text="amount">None</p>
                        </div>
                        <div class="flex justify-end space-x-1">
                            <button type="button" @click="step5 = false, step4 = true" class="btn btn-ghost btn-primary">Back</button>
                            <button @click="loader = true" type="submit" class="btn btn-primary">Send Now</button>
                        </div>
                        <x-loader />
                    </div>
                    <div class="mockup-phone">
                        <div class="camera"></div> 
                        <div class="display">
                            <div class="artboard artboard-demo phone-1"> 
                                <img src="{{asset('images/payment/sample-gcash-receipt.jpg')}}" class="show_img" />
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
