/**
 * Lenis smooth scrolling, driven by the GSAP ticker so ScrollTrigger
 * animations follow it frame by frame.
 *
 * Off for reduced motion and on the checkout / my account apps (forms,
 * validation scroll-to-error, hash router). Touch devices keep native
 * scrolling (Lenis' default).
 */
import Lenis from 'lenis';
import 'lenis/dist/lenis.css';
import {gsap} from 'gsap';
import {ScrollTrigger} from 'gsap/ScrollTrigger';

const EXCLUDED_TEMPLATES = ['page-template-checkout', 'page-template-my-account'];

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const excluded = EXCLUDED_TEMPLATES.some((cls) => document.body.classList.contains(cls));

if (!reducedMotion && !excluded) {
	gsap.registerPlugin(ScrollTrigger);

	const lenis = new Lenis({
		lerp: 0.1,
		anchors: true
	});

	lenis.on('scroll', ScrollTrigger.update);
	gsap.ticker.add((time) => lenis.raf(time * 1000));
	gsap.ticker.lagSmoothing(0);
}
