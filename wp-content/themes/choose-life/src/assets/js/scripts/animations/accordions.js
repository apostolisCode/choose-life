/**
 * Smooth open / close for the <details> accordions (FAQ, bank accounts).
 * Works without JS too: the native <details> behaviour is the fallback.
 */
import {gsap} from 'gsap';

const ACCORDIONS = [
	{item: '.faq__item', content: '.faq__answer'},
	{item: '.bank-account', content: '.bank-account__content'},
];

export default function initAccordions() {
	ACCORDIONS.forEach(({item, content}) => {
		document.querySelectorAll(item).forEach((details) => {
			const summary = details.querySelector('summary');
			const panel = details.querySelector(content);
			if (!summary || !panel) {
				return;
			}

			summary.addEventListener('click', (e) => {
				e.preventDefault();
				gsap.killTweensOf(panel);

				if (details.open && !details.classList.contains('is-closing')) {
					// closing: animate first, remove [open] at the end
					details.classList.add('is-closing');
					gsap.to(panel, {
						height: 0,
						paddingBottom: 0,
						opacity: 0,
						duration: .35,
						ease: 'power2.inOut',
						onComplete: () => {
							details.open = false;
							details.classList.remove('is-closing');
							gsap.set(panel, {clearProps: 'height,paddingBottom,opacity'});
						}
					});
				} else {
					details.classList.remove('is-closing');
					details.open = true;
					gsap.from(panel, {
						height: 0,
						paddingBottom: 0,
						opacity: 0,
						duration: .45,
						ease: 'power2.out',
						onComplete: () => gsap.set(panel, {clearProps: 'height,paddingBottom,opacity'})
					});
				}
			});
		});
	});
}
