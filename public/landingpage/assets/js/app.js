var swiper = new Swiper('#section-4 .swiper-container', {
  effect: 'coverflow',
  grabCursor: true,
  centeredSlides: 1,
  slidesPerView: 'auto',
  coverflowEffect: {
    rotate: 40,
    stretch: 0,
    depth: 100,
    modifier: -1,
    slideShadows: false,
  },
  spaceBetween: 30,
  grabCursor: true,
  loop: true,
  autoplay: {
    delay: 1000,
  },
  breakpoints: {
    480: {
      slidesPerView: 1,
      spaceBetween: 20,
    },
    640: {
      slidesPerView: 2,
      spaceBetween: 20,
    },
    768: {
      slidesPerView: 2,
      spaceBetween: 40,
    },
    1024: {
      slidesPerView: 3,
      spaceBetween: 50,
    },
  }
});
var swiper = new Swiper('#section-5 .swiper-container', {
  effect: 'coverflow',
  grabCursor: true,
  centeredSlides: 1,
  slidesPerView: 'auto',
  coverflowEffect: {
    rotate: 20,
    stretch: 1,
    depth: 100,
    modifier:-1,
    slideShadows: false,
  },
  spaceBetween: 30,
  grabCursor: true,
  loop: true,
  autoplay: {
    delay: 4000,
    speed:1000
  },
  breakpoints: {
    480: {
      slidesPerView: 1,
      spaceBetween: 20,
    },
    640: {
      slidesPerView: 1,
      spaceBetween: 20,
    },
    768: {
      slidesPerView: 2,
      spaceBetween: 40,
    },
    1024: {
      slidesPerView: 3,
      spaceBetween: 50,
    },
  }
});

