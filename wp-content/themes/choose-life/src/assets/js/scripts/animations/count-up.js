/**
 * Figures ([data-count-up], e.g. "13K", "42", "1.500+") count up from zero
 * once they come into view; the text around the number is kept.
 */
import {gsap} from 'gsap';

export default function initCountUp() {
	gsap.utils.toArray('[data-count-up]').forEach((el) => {
		const match = el.textContent.trim().match(/^(\D*)(\d+(?:[.,]\d+)?)(.*)$/);
		if (!match) {
			return;
		}

		const [, prefix, number, suffix] = match;
		const separator = number.includes(',') ? ',' : '.';
		const decimals = (number.split(/[.,]/)[1] || '').length;
		const target = parseFloat(number.replace(',', '.'));
		const render = (value) => {
			el.textContent = prefix + value.toFixed(decimals).replace('.', separator) + suffix;
		};

		const state = {value: 0};
		render(0);
		gsap.to(state, {
			value: target,
			duration: 1.6,
			ease: 'power2.out',
			onUpdate: () => render(state.value),
			scrollTrigger: {trigger: el, start: 'top 85%', once: true}
		});
	});
}
