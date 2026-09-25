/**
 * Journeys of hope (elements/sections/journeys-globe.php): list, details
 * panel, filters and a tour; the WebGL globe (./globe, three.js) is loaded
 * when the section comes near the viewport and only if WebGL is available.
 */

const TOUR_DELAY = 8000;

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function supportsWebGL() {
	try {
		return !!document.createElement('canvas').getContext('webgl2');
	} catch {
		return false;
	}
}

function init(root, data) {
	const journeys = data.journeys || [];
	if (!journeys.length) return;

	const byId = new Map(journeys.map((j) => [j.id, j]));
	const $ = (selector) => root.querySelector(selector);
	const stage = $('[data-journeys-stage]');
	const panel = $('[data-journeys-panel]');
	const list = $('[data-journeys-list]');
	const items = [...root.querySelectorAll('[data-journey-item]')];
	const cards = [...root.querySelectorAll('[data-journey]')];
	const filtersToggle = $('[data-journeys-filters-toggle]');
	const filtersBox = $('[data-journeys-filters]');
	const filtersCount = $('[data-journeys-filters-count]');
	const empty = $('[data-journeys-empty]');
	const thumb = $('[data-journeys-thumb]');
	const hint = $('[data-journeys-hint]');

	let activeId = journeys[0].id;
	let visible = journeys.map((j) => j.id);
	let globe = null;
	let interacted = false;
	let inView = false;
	let tourTimer = null;

	// ─── details panel ──────────────────────────────────────────────────

	const placeLabel = (place) => (place.city ? `${place.city}, ${place.country_name}` : place.country_name);

	function fill(journey) {
		const field = (name) => panel.querySelector(`[data-field="${name}"]`);
		field('title').textContent = journey.title;
		field('donor').textContent = journey.donor ? placeLabel(journey.donor) : '';
		field('hospital').textContent = journey.hospital ? `${journey.hospital.hospital}, ${journey.hospital.name}` : '';
		panel.querySelector('[data-field-row="hospital"]').hidden = !journey.hospital;
		field('patient').textContent = placeLabel(journey.patient);
		field('story').innerHTML = journey.story || '';   // escaped + wpautop'd by PHP
		field('from').textContent = (journey.hospital || journey.donor).name;
		field('to').textContent = journey.patient.name;
	}

	function scrollListTo(item) {
		const horizontal = list.scrollWidth > list.clientWidth + 1 && getComputedStyle(list).overflowX !== 'hidden';
		const behavior = reducedMotion ? 'auto' : 'smooth';
		if (horizontal) {
			list.scrollTo({left: item.offsetLeft - (list.clientWidth - item.offsetWidth) / 2, behavior});
		} else {
			list.scrollTo({top: item.offsetTop - (list.clientHeight - item.offsetHeight) / 2, behavior});
		}
	}

	function select(id, {fromTour = false} = {}) {
		const journey = byId.get(id);
		if (!journey) return;

		activeId = id;
		fill(journey);
		panel.hidden = false;
		panel.classList.remove('is-changing');
		void panel.offsetWidth;   // restart the fade
		panel.classList.add('is-changing');

		cards.forEach((card) => {
			const active = Number(card.dataset.journey) === id;
			card.classList.toggle('is-active', active);
			card.setAttribute('aria-pressed', String(active));
			if (active) scrollListTo(card.closest('li'));
		});

		globe?.select(id);
		if (!fromTour) stopTour();
	}

	function close() {
		activeId = null;
		panel.hidden = true;
		cards.forEach((card) => {
			card.classList.remove('is-active');
			card.setAttribute('aria-pressed', 'false');
		});
		globe?.clearSelection();
		stopTour();
	}

	cards.forEach((card) => card.addEventListener('click', () => select(Number(card.dataset.journey))));
	$('[data-journeys-close]').addEventListener('click', close);
	root.addEventListener('keydown', (e) => {
		if (e.key === 'Escape') {
			if (!filtersBox.hidden) toggleFilters(false);
			else if (activeId) close();
		}
	});

	// ─── filters ────────────────────────────────────────────────────────

	const checks = [...filtersBox.querySelectorAll('input[type="checkbox"]')];

	function toggleFilters(open) {
		filtersBox.hidden = !open;
		filtersToggle.setAttribute('aria-expanded', String(open));
		if (open) checks[0]?.focus();
	}

	function applyFilters() {
		const chosen = {country: new Set(), hospital: new Set()};
		checks.filter((c) => c.checked).forEach((c) => chosen[c.name].add(c.value));
		const matches = (item) =>
			(!chosen.country.size || chosen.country.has(item.dataset.country)) &&
			(!chosen.hospital.size || chosen.hospital.has(item.dataset.hospital));

		visible = [];
		items.forEach((item) => {
			const show = matches(item);
			item.hidden = !show;
			if (show) visible.push(Number(item.dataset.journeyItem));
		});
		empty.hidden = visible.length > 0;

		const count = chosen.country.size + chosen.hospital.size;
		filtersCount.hidden = !count;
		filtersCount.textContent = count;

		globe?.setVisible(visible);
		if (activeId && !visible.includes(activeId)) {
			visible.length ? select(visible[0]) : close();
		}
		updateScrollbar();
	}

	if (checks.length) {
		filtersToggle.hidden = false;
		filtersToggle.addEventListener('click', () => toggleFilters(filtersBox.hidden));
		checks.forEach((c) => c.addEventListener('change', () => {
			stopTour();
			applyFilters();
		}));
		$('[data-journeys-filters-clear]').addEventListener('click', () => {
			checks.forEach((c) => (c.checked = false));
			applyFilters();
		});
		document.addEventListener('click', (e) => {
			if (!filtersBox.hidden && !filtersBox.contains(e.target) && !filtersToggle.contains(e.target)) {
				toggleFilters(false);
			}
		});
	}

	// ─── list scrollbar (the native one is hidden) ──────────────────────

	function updateScrollbar() {
		const ratio = list.clientHeight / list.scrollHeight;
		thumb.parentElement.hidden = ratio >= 1;
		if (ratio >= 1) return;
		const height = Math.max(40, ratio * list.clientHeight);
		const top = (list.scrollTop / (list.scrollHeight - list.clientHeight)) * (list.clientHeight - height);
		thumb.style.height = `${height}px`;
		thumb.style.transform = `translateY(${top}px)`;
	}

	list.addEventListener('scroll', updateScrollbar, {passive: true});
	new ResizeObserver(updateScrollbar).observe(list);

	// ─── look of the globe (?globe=, selector for editors) ──────────────

	let theme = data.theme || data.default;
	const themeButtons = [...root.querySelectorAll('[data-journeys-theme]')];

	function setTheme(name) {
		root.classList.replace(`journeys--${theme}`, `journeys--${name}`);
		theme = name;
		themeButtons.forEach((button) => button.setAttribute('aria-pressed', String(button.dataset.journeysTheme === name)));
		globe?.setTheme(name);

		// shareable: keep it in the address
		const url = new URL(window.location.href);
		name === data.default ? url.searchParams.delete('globe') : url.searchParams.set('globe', name);
		history.replaceState(history.state, '', url);
	}

	themeButtons.forEach((button) => button.addEventListener('click', () => setTheme(button.dataset.journeysTheme)));

	// ─── tour: step through the journeys until the visitor takes over ───

	function stopTour() {
		interacted = true;
		clearInterval(tourTimer);
		tourTimer = null;
		hint && (hint.hidden = true);
	}

	function startTour() {
		if (reducedMotion || interacted || tourTimer) return;
		tourTimer = setInterval(() => {
			if (!inView || document.hidden || !visible.length) return;
			const next = visible[(visible.indexOf(activeId) + 1) % visible.length];
			select(next, {fromTour: true});
		}, TOUR_DELAY);
	}

	['pointerdown', 'wheel', 'keydown'].forEach((type) => panel.addEventListener(type, stopTour, {passive: true}));
	list.addEventListener('pointerdown', stopTour, {passive: true});

	// ─── globe ──────────────────────────────────────────────────────────

	const viewObserver = new IntersectionObserver(([entry]) => {
		inView = entry.isIntersecting;
		globe?.setInView(inView);
	}, {threshold: 0.05});
	viewObserver.observe(root);

	if (!supportsWebGL()) {
		root.classList.add('is-static');
		return;
	}

	const loadObserver = new IntersectionObserver(([entry]) => {
		if (!entry.isIntersecting) return;
		loadObserver.disconnect();

		import(/* webpackChunkName: "journeys-globe" */ './globe').then(({default: JourneyGlobe}) => {
			globe = new JourneyGlobe(stage, $('[data-journeys-overlay]'), {
				journeys,
				textures: data.textures,
				icons: {sample: data.icons.sample},
				theme,
				reducedMotion,
				onSelect: (id) => select(id),
				onInteract: stopTour,
				onReady: () => {
					root.classList.add('is-ready');
					if (hint && !interacted) hint.hidden = false;
					startTour();
				},
			});
			globe.setInView(inView);
			globe.setVisible(visible);
			if (activeId) globe.select(activeId);
		}).catch(() => root.classList.add('is-static'));
	}, {rootMargin: '400px 0px'});
	loadObserver.observe(root);
}

const root = document.querySelector('[data-journeys]');
if (root && window.journeys_globe) {
	init(root, window.journeys_globe);
}
