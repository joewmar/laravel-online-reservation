<x-landing-layout noFooter>
    <x-full-content>
        <form action="{{route('reservation.payment.store', encrypt($reservation->id))}}" method="POST" enctype="multipart/form-data">
            @csrf
            <div x-data="{all: true, step1: false, step2: false, step3: false, paypalName: '', refNo: '', amount: ''}" x-cloak>
                <div x-show="all" class="flex flex-col-reverse md:flex-row justify-center items-center w-full h-screen space-y-2 md:space-x-3 py-10">
                    <div  class="rounded text-sm md:text-xl mt-5">
                        <p class="text-neutral"><span class="font-bold">Step 1: </span>Make your payment at the nearest or preferred bank location. Don't forget to keep your receipt.</p>
                        <p class="text-neutral"><span class="font-bold">Step 2: </span>Send Your Screenshot of your Receipt</p>
                        <p class="text-neutral"><span class="font-bold">Step 3: </span>Fill up the information for verify your payment</p>
                        <div class="flex justify-start md:justify-end mt-5">
                            <button type="button" @click="all = false, step2 = true" class="btn btn-primary">Proceed</button>
                        </div>
                    </div>
                </div>
                <div x-show="step2" class="flex flex-col-reverse md:flex-row  justify-center items-center w-full h-full space-y-2 md:space-x-3 py-10">
                    <div class="w-96 rounded text-sm md:text-xl mt-5 space-y-5">
                        <p class="text-neutral"><span class="font-bold">Step 1: </span>Make your payment at the nearest or preferred bank location. Don't forget to keep your receipt.</p>
                        <p class="text-neutral mb-5"><span class="font-bold">Step 2: </span>Send Screenshot of Receipt of Gcash and Fill up the information for verify your payment</p>
                        <div class="form-control w-full">
                            <label class="label">
                            <span class="label-text text-neutral">Send Screenshot of Receipt</span>
                            </label>
                            <input type="file" id="image" name="image"  class="file-input file-input-bordered file-input-primary file-input-sm md:file-input-md w-full"/>
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
                            <x-input xModel="paypalName" placeholder="Payer" name="payment_name" id="payment_name" />
                        </div>
                        <div class="flex justify-start md:justify-end mt-5 space-x-1">
                            <button type="button" @click="all = true, step2 = false" class="btn btn-ghost btn-primary">Back</button>
                            <button id="done" type="button" @click="step2 = false, step3 = true" class="btn btn-primary">Go Step 2</button>
                        </div>
                    </div>
                    <div class="mockup-phone">
                        <div class="camera"></div> 
                        <div class="display">
                            <div class="artboard artboard-demo phone-1"> 
                                <img src="{{asset('images/payment/bank-transfer.png')}}" class="show_img" />
                            </div>
                        </div>
                    </div>
                </div>
                <div x-show="step3" class="flex flex-col-reverse md:flex-row  justify-center items-center w-full h-full space-y-2 md:space-x-3 py-10">
                    <div x-data="{loader: false}" class="w-96 rounded text-sm md:text-xl mt-5">
                        <p class="text-neutral mb-5"><span class="font-bold">Step 4: </span>Confirm the information</p>
                        <p class="text-neutral"><span class="font-bold">Receipt Name: </span><span x-text="paypalName">None</p>
                        <p class="text-neutral"><span class="font-bold">Transaction ID: </span><span x-text="refNo">None</p>
                        <p class="text-neutral"><span class="font-bold">Total Amount </span><span x-text="amount">None</p>
                        <div class="flex justify-start md:justify-end mt-5 space-x-1">
                            <button type="button" @click="step3 = false, step2 = true" class="btn btn-ghost btn-primary">Back</button>
                            <button @click="loader = true" type="submit" class="btn btn-primary">Send Now</button>
                        </div>
                        <x-loader />
                    </div>
                    <div class="mockup-phone">
                        <div class="camera"></div> 
                        <div class="display">
                            <div class="artboard artboard-demo phone-1"> 
                                <img src="{{asset('images/payment/bank-transfer.png')}}" class="show_img" />
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
