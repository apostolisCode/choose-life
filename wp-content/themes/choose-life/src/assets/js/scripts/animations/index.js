/**
 * GSAP animations. Skipped entirely for visitors who prefer reduced motion.
 */
import {gsap} from 'gsap';
import {ScrollTrigger} from 'gsap/ScrollTrigger';
import initWordsBands from './words-bands';
import initAccordions from './accordions';
import initReveal from './reveal';
import initContactCard from './contact-card';

gsap.registerPlugin(ScrollTrigger);

if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
	initWordsBands();
	initAccordions();
	initReveal();
	initContactCard();
}
