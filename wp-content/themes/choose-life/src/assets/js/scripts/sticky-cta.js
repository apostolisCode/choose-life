/**
 * Floating "support our work" bar (elements/parts/sticky-cta.php): hidden
 * while the footer, which has the same bar, is on screen.
 */
const bar = document.querySelector('[data-sticky-cta]');
const footer = document.querySelector('.site-footer');

if (bar && footer && 'IntersectionObserver' in window) {
	new IntersectionObserver(([entry]) => {
		bar.classList.toggle('is-hidden', entry.isIntersecting);
	}).observe(footer);
}
