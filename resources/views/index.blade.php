<x-landing-layout>
  <x-navbar :activeNav="$activeNav"/>

{{-- Banner --}}
<div class="swiper swiper-home mySwiper absolute">
  <div class="swiper-wrapper">
    <div class="swiper-slide" style="background-image: url({{ asset('./images/main-hero1.jpg')}});"></div>
    <div class="swiper-slide" style="background-image: url({{ asset('./images/main-hero2.jpg')}});"></div>
    <div class="swiper-slide" style="background-image: url({{ asset('./images/main-hero3.jpg')}});"></div>
    <div class="swiper-slide" style="background-image: url({{ asset('./images/main-hero4.jpg')}});"></div>
    <div class="swiper-slide" style="background-image: url({{ asset('./images/main-hero5.jpg')}});"></div>
    <div class="swiper-slide" style="background-image: url({{ asset('./images/main-hero6.jpg')}});"></div>
  </div>
</div>
<section class="hero min-h-screen swiper mySwiper">
  <div class="hero-overlay bg-opacity-70"></div>
  <div class="hero-content text-center text-white ">
    <div class="max-w-md">
      <h1 class="mb-5 text-5xl font-bold">Hello there</h1>
      <p class="mb-5">Provident cupiditate voluptatem et in. Quaerat fugiat ut assumenda excepturi exercitationem quasi. In deleniti eaque aut repudiandae et a id nisi.</p>
      {{-- <button class="btn btn-primary">Get Started</button> --}}
    </div>
  </div>

  {{-- Slider button --}}
  <div class="swiper-pagination"></div>

</section>

@include('news');

  <section >
    <div class="mx-auto max-w-screen-xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
      <div class="max-w-3xl">
        <h2 class="text-3xl font-bold sm:text-4xl">
          Lorem ipsum, dolor sit amet consectetur adipisicing elit. Quod alias
          doloribus impedit.
        </h2>
      </div>
  
      <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-2 lg:gap-16">
        <div class="relative h-64 overflow-hidden sm:h-80 lg:h-full">
          <img
            alt="Party"
            src="https://images.unsplash.com/photo-1496843916299-590492c751f4?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1771&q=80"
            class="absolute inset-0 h-full w-full object-cover"
          />
        </div>
  
        <div class="lg:py-16">
          <article class="space-y-4 text-neutral">
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

  




</x-landing-layout>