/**
 * Share button without a share link (single.php, Instagram): opens the
 * device's share sheet, or copies the link where there is none; both with the
 * default text (data-share-text: the action's, else Theme Options → Share).
 */
document.querySelectorAll('[data-share]').forEach((button) => {
	const url = button.dataset.share;
	const text = button.dataset.shareText || '';
	const copied = button.querySelector('[data-share-copied]');
	let timer;

	button.addEventListener('click', async () => {
		if (navigator.share) {
			try {
				await navigator.share({title: button.dataset.shareTitle || document.title, text, url});
			} catch (e) {
				// closed by the visitor
			}
			return;
		}
		try {
			await navigator.clipboard.writeText(text ? text + ' ' + url : url);
			if (copied) {
				copied.hidden = false;
				clearTimeout(timer);
				timer = setTimeout(() => copied.hidden = true, 2000);
			}
		} catch (e) {
			window.prompt('', text ? text + ' ' + url : url);
		}
	});
});
