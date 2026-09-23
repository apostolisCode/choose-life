/**
 * Scroll reveal: [data-reveal] fades / slides in once when it enters the
 * viewport; the children of [data-reveal-stagger] come in one after another.
 */
import {gsap} from 'gsap';
import {ScrollTrigger} from 'gsap/ScrollTrigger';

export default function initReveal() {
	gsap.utils.toArray('[data-reveal]').forEach((el) => {
		gsap.from(el, {
			y: 48,
			autoAlpha: 0,
			duration: .9,
			ease: 'power3.out',
			scrollTrigger: {trigger: el, start: 'top 85%', once: true}
		});
	});

	gsap.utils.toArray('[data-reveal-stagger]').forEach((group) => {
		gsap.from(group.children, {
			y: 64,
			autoAlpha: 0,
			duration: .9,
			ease: 'power3.out',
			stagger: .15,
			scrollTrigger: {trigger: group, start: 'top 85%', once: true}
		});
	});

	// images loading later change the page height
	window.addEventListener('load', () => ScrollTrigger.refresh());
}
