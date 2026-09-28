/**
 * 360° tour (elements/sections/tour-360.php): the cover image and the "360°"
 * button are swapped for the tour's iframe on click, so the (heavy) viewer
 * loads only when asked for and doesn't catch the page scroll before that.
 */
document.querySelectorAll('[data-tour-360]').forEach((frame) => {
	const play = frame.querySelector('[data-tour-360-play]');
	if (!play) {
		return;
	}

	play.addEventListener('click', () => {
		const iframe = document.createElement('iframe');
		iframe.className = 'tour-360__iframe';
		// not dataset: "data-tour-360" has no camelCase key (a digit follows the dash)
		iframe.src = frame.getAttribute('data-tour-360');
		iframe.title = frame.dataset.tourTitle || '';
		iframe.allow = 'fullscreen; accelerometer; gyroscope; magnetometer; xr-spatial-tracking';
		iframe.allowFullscreen = true;
		frame.replaceChildren(iframe);
		frame.classList.add('is-playing');
		iframe.focus();
	}, {once: true});
});
