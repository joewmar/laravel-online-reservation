
<x-landing-layout>
  @push('styles')
  <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-element-bundle.min.js"></script>  

  <style>
    html,
    body {
      position: relative;
      height: 100%;
    }

    body {
      margin: 0;
      padding: 0;
    }
    swiper-container {
      width: 100%;
      height: 100%;
    }

    swiper-slide {
      text-align: center;
      font-size: 18px;
      background: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    swiper-slide img {
      display: block;
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
  </style>
  @endpush

  <x-navbar :activeNav="$activeNav"/>

{{-- Banner --}}
<swiper-container class="landingSlider" pagination="true" pagination-clickable="true" effect="fade" autoplay-delay="2500" autoplay-disable-on-interaction="false">
    <swiper-slide><img loading="lazy" src="{{ asset('./images/main-hero2.jpg')}}" /></swiper-slide>
    <swiper-slide><img loading="lazy" src="{{ asset('./images/main-hero3.jpg')}}" /></swiper-slide>
    <swiper-slide><img loading="lazy" src="{{ asset('./images/main-hero4.jpg')}}" /></swiper-slide>
    <swiper-slide><img loading="lazy" src="{{ asset('./images/main-hero5.jpg')}}" /></swiper-slide>
    <swiper-slide><img loading="lazy" src="{{ asset('./images/main-hero6.jpg')}}" /></swiper-slide>
    <section class="hero min-h-screen absolute top-0">
      <div class="hero-overlay bg-opacity-70"></div>
      <div class="hero-content text-center text-white ">
        <div class="max-w-md">
          <h1 class="mb-5 text-5xl font-bold">Hello there</h1>
          <p class="mb-5">Discover the Perfect Blend of Comfort and Adventure by Reserving Your Ideal Guesthouse Stay and Guided Tour with Us. Every Moment Becomes a Cherished Memory.</p>
          <label for="reservation" class="btn btn-primary">Let's Book</label>
        </div>
      </div>
    </section>
</swiper-container>

{{-- @include('news'); --}}

  <section  data-aos="fade-down" data-aos-anchor-placement="center-bottom">
    <div class="mx-auto max-w-screen-xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
      <div class="max-w-3xl">
        <h2 class="text-3xl font-bold sm:text-4xl">
          Lorem ipsum, dolor sit amet consectetur adipisicing elit. Quod alias
          doloribus impedit.
        </h2>
      </div>
  
      <div data-aos="fade-up-right" class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-2 lg:gap-16">
        <div class="relative h-64 overflow-hidden sm:h-80 lg:h-full" >
          <img
            alt="Party"
            src="https://images.unsplash.com/photo-1496843916299-590492c751f4?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1771&q=80"
            class="absolute inset-0 h-full w-full object-cover"
            
          />
        </div>
  
        <div class="lg:py-16" data-aos="fade-left">
          <article class="space-y-4 text-neutral" >
            <p>
              Lorem ipsum dolor, sit amet consectetur adipisicing elit. Aut qui
              hic atque tenetur quis eius quos ea neque sunt, accusantium soluta
              minus veniam tempora deserunt? Molestiae eius quidem quam repellat.
            </p>
  
            <p>
              Lorem ipsum dolor sit amet consectetur, adipisicing elit. Dolorum
              explicabo quidem voluptatum voluptas illo accusantium ipsam quis,
              vel mollitia? Vel provident culpa dignissimos possimus, perferendis
              consectetur odit accusantium dolorem amet voluptates aliquid,
              ducimus tempore incidunt quas. Veritatis molestias tempora
              distinctio voluptates sint! Itaque quasi corrupti, sequi quo odit
              illum impedit!
            </p>
          </article>
        </div>

      </div>
    </div>
  </section>

  @include('offers')

  @include('procedure')

  @include('testimonials')

  @include('gallery')

  

  <x-date-modal id="reservation" operation="{{$web_contents->operation}}" />

  @push('scripts')
    <script src="{{Vite::asset("resources/js/navbar.js")}}"></script>
  @endpush
</x-landing-layout>