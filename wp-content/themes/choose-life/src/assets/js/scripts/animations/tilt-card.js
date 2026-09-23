/**
 * Red card behind a card ([data-tilt-card] > [data-tilt-card-back]) that
 * tilts out while the card scrolls into view; data-tilt sets the angle.
 */
import {gsap} from 'gsap';

export default function initTiltCards() {
	gsap.utils.toArray('[data-tilt-card]').forEach((card) => {
		const back = card.querySelector('[data-tilt-card-back]');
		if (!back) {
			return;
		}

		gsap.fromTo(back, {rotate: 0}, {
			rotate: parseFloat(card.dataset.tilt) || -2.5,
			ease: 'none',
			scrollTrigger: {trigger: card, start: 'top 90%', end: 'top 20%', scrub: .6}
		});
	});
}
