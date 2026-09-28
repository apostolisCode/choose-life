/**
 * Featured actions carousel (elements/sections/actions-featured.php): one
 * wide card at a time, swiped / dragged, with dots and autoplay.
 */
import Swiper from 'swiper';
import {A11y, Autoplay, Keyboard, Pagination} from 'swiper/modules';

document.querySelectorAll('[data-actions-carousel]').forEach((el) => {
	if (el.querySelectorAll('.swiper-slide').length < 2) {
		return;
	}

	new Swiper(el, {
		modules: [A11y, Autoplay, Keyboard, Pagination],
		slidesPerView: 1,
		spaceBetween: 26.667,
		grabCursor: true,
		speed: 700,
		keyboard: {enabled: true, onlyInViewport: true},
		pagination: {el: el.querySelector('[data-actions-carousel-dots]'), clickable: true},
		autoplay: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? false : {
			delay: 6000,
			pauseOnMouseEnter: true,
			disableOnInteraction: true
		}
	});
});
