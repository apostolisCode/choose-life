// Mobile menu toggle for the site header
const toggle = document.querySelector('.site-header__toggle');
const panel = document.getElementById('site-header-panel');

if (toggle && panel) {
	const setOpen = (open) => {
		toggle.setAttribute('aria-expanded', String(open));
		panel.hidden = !open;
	};

	toggle.addEventListener('click', () => {
		setOpen(toggle.getAttribute('aria-expanded') !== 'true');
	});

	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape' && !panel.hidden) {
			setOpen(false);
			toggle.focus();
		}
	});

	window.matchMedia('(min-width: 1400px)').addEventListener('change', (e) => {
		if (e.matches) {
			setOpen(false);
		}
	});
}
