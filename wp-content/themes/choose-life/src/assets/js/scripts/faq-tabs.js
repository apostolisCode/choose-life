/**
 * FAQ page (templates/faq.php): category tabs and "view all" per tab.
 * Without JS every category and question is simply listed.
 */
import {gsap} from 'gsap';

const reduceMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const fadeIn = (targets, stagger = 0) => {
	if (!reduceMotion()) {
		gsap.fromTo(targets, {autoAlpha: 0, y: 16}, {autoAlpha: 1, y: 0, duration: .4, ease: 'power2.out', stagger, clearProps: 'all'});
	}
};

document.querySelectorAll('[data-faq-tabs]').forEach((root) => {
	const tabs = [...root.querySelectorAll('[role="tab"]')];
	const panels = [...root.querySelectorAll('[data-faq-panel]')];

	// "view all": the questions after the first few are revealed on demand
	panels.forEach((panel) => {
		const extras = [...panel.querySelectorAll('[data-faq-extra]')];
		const more = panel.querySelector('[data-faq-more]');
		extras.forEach(item => item.hidden = true);
		if (more) {
			more.addEventListener('click', () => {
				extras.forEach(item => item.hidden = false);
				fadeIn(extras, .06);
				more.remove();
			});
		}
	});

	if (!tabs.length) {
		return;
	}

	const select = (index, focus = false) => {
		tabs.forEach((tab, i) => {
			const active = i === index;
			tab.classList.toggle('is-active', active);
			tab.setAttribute('aria-selected', String(active));
			tab.tabIndex = active ? 0 : -1;
			panels[i].hidden = !active;
		});
		if (focus) {
			tabs[index].focus();
		}
		fadeIn(panels[index]);
	};

	tabs.forEach((tab, i) => {
		tab.addEventListener('click', () => {
			if (!tab.classList.contains('is-active')) {
				select(i);
			}
		});
		// arrow keys move between tabs (WAI-ARIA tabs pattern)
		tab.addEventListener('keydown', (e) => {
			const moves = {ArrowRight: 1, ArrowLeft: -1, Home: -i, End: tabs.length - 1 - i};
			if (e.key in moves) {
				e.preventDefault();
				select((i + moves[e.key] + tabs.length) % tabs.length, true);
			}
		});
	});

	panels.forEach((panel, i) => panel.hidden = i !== 0);
});
