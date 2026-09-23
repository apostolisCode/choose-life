/**
 * Contact page: the red card behind the form tilts out while the form
 * scrolls into view.
 */
import {gsap} from 'gsap';

export default function initContactCard() {
	gsap.utils.toArray('[data-contact-card]').forEach((card) => {
		const back = card.querySelector('.contact-page__card-back');
		if (!back) {
			return;
		}

		gsap.fromTo(back, {rotate: 0}, {
			rotate: -2.5,
			ease: 'none',
			scrollTrigger: {trigger: card, start: 'top 90%', end: 'top 20%', scrub: .6}
		});
	});
}
