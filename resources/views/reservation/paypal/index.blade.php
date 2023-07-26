<x-landing-layout>
    <x-full-content>
        <form action="{{route('reservation.payment.store', encrypt($reservation->id))}}" method="POST" enctype="multipart/form-data">
            @csrf
            <div x-data="{all: true, step1: false, step2: false, step3: false, step4: false, gcashName: '', refNo: '', amount: ''}" x-cloak>
                <div x-show="all" class="flex flex-col-reverse md:flex-row justify-center items-center w-full h-full space-y-2 md:space-x-3">
                    <div  class="w-96 rounded">
                        <p class="text-neutral text-2xl"><span class="font-bold">Step 1: </span>Pay via QR Scanner</p>
                        <p class="text-neutral text-2xl"><span class="font-bold">Step 2: </span>Send Screenshot of Receipt of Gcash</p>
                        <p class="text-neutral text-2xl"><span class="font-bold">Step 3: </span>Fill up the information for verify your payment</p>
                        <div class="flex justify-end">
                            <button type="button" @click="all = false, step1 = true" class="btn btn-primary">Proceed</button>
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
                <div x-show="step1" class="flex flex-col-reverse md:flex-row  justify-center items-center w-full h-full space-y-2 md:space-x-3">
                    <div  class="w-96 rounded">
                        <p class="text-neutral text-2xl"><span class="font-bold">Step 1: </span>Pay via QR Scanner</p>
                        <ul class="text-neutral text-lg mt-5">
                            <li><span class="font-bold">Gcash Name: </span>Juan D.</li>
                            <li><span class="font-bold">Gcash No: </span>09123456789</li>
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
                                <img src="{{asset('images/payment/gcash.jpg')}}" />
                            </div>
                        </div>
                    </div>
                </div>
                <div x-show="step2" class="flex flex-col-reverse md:flex-row  justify-center items-center w-full h-full space-y-2 md:space-x-3">
                    <div  class="w-96 rounded">
                        <p class="text-neutral text-2xl"><span class="font-bold">Step 2: </span>Send Screenshot of Receipt of Gcash</p>
                        <div class="form-control w-full mt-5">
                            <label class="label">
                            <span class="label-text text-neutral">Send Screenshot here</span>
                            </label>
                            <input type="file" id="image" name="image"  class="file-input file-input-bordered file-input-primary w-full"/>
                            <label class="label">
                                @error('image')
                                    <span class="label-text">{{$message}}</span>
                                @enderror
                                <span class="label-text-alt">We have sample below here</span>
                            </label>
                        </div>
                        <div class="flex justify-end space-x-1">
                            <button type="button" @click="step1 = true, step2 = false" class="btn btn-ghost btn-primary">Back</button>
                            <button type="button" @click="step2 = false, step3 = true" class="btn btn-primary">Go Step 3</button>
                        </div>
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
                <div x-show="step3" class="flex flex-col-reverse md:flex-row  justify-center items-center w-full h-full space-y-2 md:space-x-3">
                    <div  class="w-96 rounded">
                        <p class="text-neutral text-2xl"><span class="font-bold">Step 3: </span>Fill up the information for verify your payment</p>
                        <div class="mt-5">
                            <x-input xModel="amount" type="numeric" placeholder="Total Amount" name="amount" id="amount" />
                            <x-input xModel="refNo" placeholder="Reference No." name="reference_no" id="reference_no" />
                            <x-input xModel="gcashName" placeholder="Gcash Name" name="payment_name" id="payment_name" />
                        </div>
                        <div class="flex justify-end space-x-1">
                            <button type="button" @click="step2 = true, step3 = false" class="btn btn-ghost">Back</button>
                            <button type="button" @click="step4 = true, step3 = false" class="btn btn-primary">Confirm</button>
                        </div>
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
                <div x-show="step4" class="flex flex-col-reverse md:flex-row  justify-center items-center w-full h-full space-y-2 md:space-x-3">
                    <div  class="w-96 rounded">
                        <p class="text-neutral text-2xl"><span class="font-bold">Step 4: </span>Confirm the information</p>
                        <div class="mt-5">
                            <p class="text-neutral text-lg"><span class="font-bold">Gcash Name </span><span x-text="gcashName">None</p>
                            <p class="text-neutral text-lg"><span class="font-bold">Reference No.: </span><span x-text="refNo">None</p>
                            <p class="text-neutral text-lg"><span class="font-bold">Total Amount </span><span x-text="amount">None</p>

                        </div>
                        <div class="flex justify-end space-x-1">
                            <button type="button" @click="step4 = false, step3 = true" class="btn btn-ghost btn-primary">Back</button>
                            <button type="submit" class="btn btn-primary">Send Now</button>
                        </div>
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
        <script>
            let imgElements = document.getElementsByClassName('show_img');
            let input = document.getElementById('image');
        
            input.addEventListener("change", () => {
                let files = input.files;
                for (let i = 0; i < imgElements.length; i++) {
                    console.log(files); // Tingnan kung tama ang file na nakukuha
                    console.log(imgElements[i]); // Tingnan kung tama ang image element na napipili
                    if (files[0]) {
                        imgElements[i].src = URL.createObjectURL(files[0]);
                    }
                }
            });
        </script>
    @endpush
</x-landing-layout>
