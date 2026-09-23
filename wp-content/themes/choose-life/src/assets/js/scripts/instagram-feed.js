/**
 * Instagram feed section (elements/sections/instagram-feed.php): endless,
 * draggable row of photo cards
 */
import Swiper from 'swiper';
import {A11y, Autoplay} from 'swiper/modules';

// loop mode needs about two screens of cards, so repeat the entered ones
const MIN_SLIDES = 12;

document.querySelectorAll('[data-instagram-feed]').forEach((el) => {
	const wrapper = el.querySelector('.swiper-wrapper');
	const originals = [...wrapper.children];
	if (!originals.length) {
		return;
	}
	for (let i = 0; wrapper.children.length < MIN_SLIDES; i++) {
		const clone = originals[i % originals.length].cloneNode(true);
		clone.setAttribute('aria-hidden', 'true');
		clone.querySelectorAll('a').forEach(link => link.setAttribute('tabindex', '-1'));
		wrapper.appendChild(clone);
	}
	// staggered cards: every third one sits higher
	[...wrapper.children].forEach((slide, i) => slide.classList.toggle('is-raised', i % 3 === 1));

	new Swiper(el, {
		modules: [A11y, Autoplay],
		slidesPerView: 'auto',
		centeredSlides: true,
		loop: true,
		grabCursor: true,
		speed: 800,
		autoplay: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? false : {
			delay: 3500,
			pauseOnMouseEnter: true,
			disableOnInteraction: false
		}
	});
});
