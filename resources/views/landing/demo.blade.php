@php 
  $arrAccType = ['Room Only', 'Day Tour', 'Overnight']; 
  $arrPayment = ['Gcash', 'PayPal', 'Bank Transfer'];
@endphp
<x-landing-layout noFooter>
  @push('styles')
  <style>

body {
      background: #eee;
      font-family: Helvetica Neue, Helvetica, Arial, sans-serif;
      font-size: 14px;
      color: #000;
      margin: 0;
      padding: 0;
    }

    swiper-container {
      width: 100%;
      height: 100vh;
    }

    swiper-slide {
      background-position: center;
      background-size: cover;
    }

    swiper-slide img {
      display: block;
      width: 100%;
    }

  </style>
  @endpush
    <div class="z-[100] absolute right-3 top-3">
      <a href="/" class="btn btn-circle btn-ghost text-error">✕</a>
    </div>
    <swiper-container class="mySwiper" pagination="true" pagination-clickable="true" space-between="30" effect="fade"
    navigation="true">
    <swiper-slide class="bg-base-100 flex flex-col items-center h-screen">
      <div class="card static md:absolute md:top-5 md:left-5 w-full md:w-96 bg-base-100 border-none md:border md:border-primary shadow-none md:shadow-lg">
        <div class="card-body">
          <h2 class="card-title">Step 1</h2>
          <p>Select your preferred date, the number of guests, and your preferred type of accommodation to make your reservation.</p>
        </div>
      </div>
      <img src="{{asset('images/demo/step1.1.png')}}" class="object-contain" />
    </swiper-slide>
    <swiper-slide class="bg-base-100">
      <div class="card static md:absolute md:top-5 md:left-5 w-full md:w-72  bg-base-100 border-none md:border md:border-primary shadow-none md:shadow-lg">
        <div class="card-body">
          <h2 class="card-title">Step 2</h2>
          <p>Choose the type of tour service, select your preferred payment method, and specify the number of guests who will be joining the tour.</p>
        </div>
      </div>
      <img src="{{asset('images/demo/step2.1.png')}}" class="object-contain" />

    </swiper-slide>
    <swiper-slide class="bg-base-100 flex flex-col items-center">
      <div class="card static md:absolute md:top-5 md:left-5 w-full md:w-72 bg-base-100 border-none md:border md:border-primary shadow-none md:shadow-lg">
        <div class="card-body">
          <h2 class="card-title">Step 2 (Continue)</h2>
          <p>Accommodation types allowed</p>
          <ul>
            <li class="list-disc ml-5">Day Tour</li>
            <li class="list-disc ml-5">Overnight</li>
          </ul>
        </div>
      </div>
      <img src="{{asset('images/demo/step2.2.png')}}" class="object-contain" />

    </swiper-slide>
    <swiper-slide class="bg-base-100 flex flex-col items-center">
      <div class="card static md:absolute md:top-5 md:left-5 w-full md:w-72 bg-base-100 border-none md:border md:border-primary shadow-none md:shadow-lg">
        <div class="card-body">
          <h2 class="card-title">Step 3</h2>
          <p>Verify or update your basic personal information</p>

        </div>
      </div>
      <img src="{{asset('images/demo/step3.png')}}" class="object-contain" />

    </swiper-slide>
    <swiper-slide class="bg-base-100 flex flex-col items-center">
      <div class="card static md:absolute md:top-5 md:left-5 w-full md:w-72 bg-base-100 border-none md:border md:border-primary shadow-none md:shadow-lg">
        <div class="card-body">
          <h2 class="card-title">Step 4</h2>
          <p>Verify the overall reservation information you have inputted and then wait for confirmation.</p>
        </div>
      </div>
      <img src="{{asset('images/demo/step4.1.png')}}" class="object-contain" />

    </swiper-slide>
    <swiper-slide class="bg-base-100 flex flex-col items-center">
      <div class="card static -0 md:absolute md:bottom-5 md:left-5 w-full md:w-72 bg-base-100 border-none md:border md:border-primary shadow-none md:shadow-lg">
        <div class="card-body">
          <h2 class="card-title">Step 4 (Continue)</h2>
          <p class="text-error">Note: When you don't have a valid ID, you will need to upload a picture of a valid ID for your online reservation.</p>
        </div>
      </div>
      <img src="{{asset('images/demo/step4.2.png')}}" class="object-contain" />

    </swiper-slide>
    </swiper-container>


  @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-element-bundle.min.js"></script>
  @endpush
</x-landing-layout>