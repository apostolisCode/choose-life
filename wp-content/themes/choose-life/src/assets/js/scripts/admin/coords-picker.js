/**
 * A map under each latitude / longitude pair of the journey and hospital
 * fields (ACF "donor_", "patient_", "hospital_" + "lat" / "lng"): click the
 * map, drag the pin or search a place (OpenStreetMap Nominatim) to fill them.
 *
 * Leaflet (window.L) is enqueued only on those screens, see admin_scripts().
 */

const PREFIXES = ['donor', 'patient', 'hospital'];
const GREECE = [38.6, 23.6];

const round = (n) => Math.round(n * 1e6) / 1e6;

function pinIcon(L) {
	return L.divIcon({
		className: 'cl-coords-picker__pin',
		html: '<span></span>',
		iconSize: [26, 36],
		iconAnchor: [13, 34],
	});
}

function setupPair(L, root, prefix) {
	const field = (name) => root.querySelector(`.acf-field[data-name="${prefix}_${name}"]`);
	const latField = field('lat');
	const lngField = field('lng');
	if (!latField || !lngField || latField.dataset.coordsPicker) return;
	latField.dataset.coordsPicker = '1';

	const latInput = latField.querySelector('input');
	const lngInput = lngField.querySelector('input');
	const cityInput = field('city')?.querySelector('input');
	const countrySelect = field('country')?.querySelector('select');

	// the map sets the numbers; the fields stay in the form, out of sight
	[latField, lngField].forEach((el) => el.classList.add('cl-coords-picker__source'));

	// same markup as the fields around it: rows of a table on the term edit
	// screen, divs everywhere else
	const inTable = lngField.tagName === 'TR';
	const cell = inTable ? 'td' : 'div';
	const box = document.createElement(inTable ? 'tr' : 'div');
	box.className = 'acf-field cl-coords-picker';
	box.innerHTML = `
		<${cell} class="acf-label">
			<label>Point on the map</label>
			<p class="description">Click the map or drag the pin, or search for the place. Empty: the centre of the country.</p>
		</${cell}>
		<${cell} class="acf-input">
			<div class="cl-coords-picker__search">
				<input type="search" placeholder="Search a place…">
				<button type="button" class="button" data-search>Search</button>
				<button type="button" class="button-link" data-clear>Clear</button>
			</div>
			<ul class="cl-coords-picker__results" hidden></ul>
			<div class="cl-coords-picker__map"></div>
		</${cell}>`;
	lngField.after(box);

	const search = box.querySelector('input[type="search"]');
	const results = box.querySelector('.cl-coords-picker__results');
	const mapEl = box.querySelector('.cl-coords-picker__map');

	const map = L.map(mapEl, {scrollWheelZoom: false}).setView(GREECE, 5);
	L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 18,
		attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a>',
	}).addTo(map);
	const marker = L.marker(GREECE, {draggable: true, icon: pinIcon(L)});

	const current = () => {
		const lat = parseFloat(latInput.value);
		const lng = parseFloat(lngInput.value);

		return Number.isFinite(lat) && Number.isFinite(lng) ? [lat, lng] : null;
	};

	const write = ([lat, lng]) => {
		latInput.value = round(lat);
		lngInput.value = round(lng);
		[latInput, lngInput].forEach((input) => input.dispatchEvent(new Event('change', {bubbles: true})));
	};

	const show = (point, zoom) => {
		if (point) {
			marker.setLatLng(point).addTo(map);
			map.setView(point, zoom ?? Math.max(map.getZoom(), 11));
		} else {
			marker.remove();
		}
	};

	map.on('click', (e) => {
		write([e.latlng.lat, e.latlng.lng]);
		show([e.latlng.lat, e.latlng.lng], map.getZoom());
	});
	marker.on('dragend', () => {
		const {lat, lng} = marker.getLatLng();
		write([lat, lng]);
	});

	box.querySelector('[data-clear]').addEventListener('click', () => {
		latInput.value = '';
		lngInput.value = '';
		show(null);
		map.setView(GREECE, 5);
	});

	// place search (Nominatim, one request per search)
	const runSearch = async () => {
		const q = search.value.trim();
		if (!q) return;
		const country = countrySelect?.value?.toLowerCase();
		const url = new URL('https://nominatim.openstreetmap.org/search');
		url.search = new URLSearchParams({q, format: 'jsonv2', limit: '6', 'accept-language': document.documentElement.lang || 'el', ...(country ? {countrycodes: country} : {})});

		results.hidden = false;
		results.innerHTML = '<li>…</li>';
		try {
			const places = await (await fetch(url)).json();
			results.innerHTML = '';
			if (!places.length) {
				results.innerHTML = '<li>No places found.</li>';
				return;
			}
			places.forEach((place) => {
				const li = document.createElement('li');
				const button = document.createElement('button');
				button.type = 'button';
				button.className = 'button-link';
				button.textContent = place.display_name;
				button.addEventListener('click', () => {
					const point = [parseFloat(place.lat), parseFloat(place.lon)];
					write(point);
					show(point, 12);
					results.hidden = true;
				});
				li.appendChild(button);
				results.appendChild(li);
			});
		} catch {
			results.innerHTML = '<li>The search is not available right now.</li>';
		}
	};
	box.querySelector('[data-search]').addEventListener('click', runSearch);
	search.addEventListener('keydown', (e) => {
		if (e.key === 'Enter') {
			e.preventDefault();   // don't submit the post form
			runSearch();
		}
	});
	search.addEventListener('focus', () => {
		if (!search.value && cityInput?.value) search.value = cityInput.value;
	});

	// the box is not an ACF field, so ACF tabs / conditional logic don't hide
	// it: follow the longitude field's "acf-hidden" class
	const syncHidden = () => box.classList.toggle('acf-hidden', lngField.classList.contains('acf-hidden'));
	new MutationObserver(syncHidden).observe(lngField, {attributes: true, attributeFilter: ['class']});
	syncHidden();

	// ACF tabs start hidden: redraw once the map gets a size
	new ResizeObserver(() => map.invalidateSize()).observe(mapEl);
	show(current(), 12);
}

export default function initCoordsPicker() {
	// after load: Leaflet and ACF are there, whatever the script order
	window.addEventListener('load', () => {
		const {L, acf} = window;
		if (!L) return;

		const setup = () => PREFIXES.forEach((prefix) => setupPair(L, document, prefix));
		setup();
		acf?.addAction('append', setup);
	});
}
