
<x-landing-layout noFooter>
  @push('styles')
  <style>
    html,
    body {
      position: relative;
      height: 100vh;
    }

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
      height: 100%;
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
    <swiper-container class="mySwiper" pagination="true" pagination-clickable="true" space-between="30" effect="fade"
    navigation="true">
    <swiper-slide>
        <img src=" https://swiperjs.com/demos/images/nature-1.jpg" class="opacity-60" />
    </swiper-slide>
    <swiper-slide>
        <img src="https://swiperjs.com/demos/images/nature-2.jpg" />
    </swiper-slide>
    <swiper-slide>
        <img src="https://swiperjs.com/demos/images/nature-3.jpg" />
    </swiper-slide>
    <swiper-slide>
        <img src="https://swiperjs.com/demos/images/nature-4.jpg" />
    </swiper-slide>
    </swiper-container>


  @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-element-bundle.min.js"></script>
  @endpush
</x-landing-layout>