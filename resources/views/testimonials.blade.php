<section class="bg-base-200">
    <div class="mx-auto max-w-screen-xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
      <h2 class="text-center text-4xl font-bold tracking-tight sm:text-5xl">
        Read trusted reviews from our customers
      </h2>
  
      <div class="mt-12 grid grid-cols-1 gap-4 md:grid-cols-3 md:gap-8">
        <x-testimonial-card />
        <x-testimonial-card />
        <x-testimonial-card />
        <x-testimonial-card />
        <x-testimonial-card />
        <x-testimonial-card />
        <x-testimonial-card />
        <x-testimonial-card />
        <x-testimonial-card />
      </div>
    </div>
  </section>
  
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const swiper = new Swiper('.swiper-container', {
        loop: true,
        slidesPerView: 1,
        spaceBetween: 32,
        centeredSlides: true,
        autoplay: {
          delay: 8000,
        },
        breakpoints: {
          640: {
            slidesPerView: 1.5,
          },
          1024: {
            slidesPerView: 3,
          },
        },
      })
    })
  </script>