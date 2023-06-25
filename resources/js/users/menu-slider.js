import Swiper from 'swiper/bundle';
// import Swiper styles
import 'swiper/css/bundle';


let swiper = new Swiper(".menuSlider", {
    slidesPerView: 2,
    grid: {
      rows: 1,
    },
    spaceBetween: 30,
    keyboard: {
        enabled: true,
      },
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      },
  });