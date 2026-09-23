/**
 * Tilted words bands (elements/sections/words-bands.php): endless horizontal
 * loop that speeds up with the scroll and follows its direction.
 */
import {gsap} from 'gsap';
import {ScrollTrigger} from 'gsap/ScrollTrigger';

export default function initWordsBands() {
	const tracks = gsap.utils.toArray('.words-band__track');
	if (!tracks.length) {
		return;
	}

	// each track holds two identical halves, so moving it by half loops seamlessly
	const loops = tracks.map((track, i) => {
		const reverse = i % 2 === 1;
		const tween = gsap.fromTo(track,
			{xPercent: reverse ? -50 : 0},
			{xPercent: reverse ? 0 : -50, duration: 60, ease: 'none', repeat: -1}
		);
		// start far into the repeats, so playing backwards never hits the start
		tween.totalTime(tween.duration() * 100);
		return tween;
	});

	let direction = 1;
	ScrollTrigger.create({
		start: 0,
		end: 'max',
		onUpdate(self) {
			direction = self.direction;
			const boost = gsap.utils.clamp(1, 6, 1 + Math.abs(self.getVelocity()) / 400);
			loops.forEach(loop => {
				gsap.killTweensOf(loop, 'timeScale');
				loop.timeScale(direction * boost);
				// ease back to the normal speed, keeping the scroll direction
				gsap.to(loop, {timeScale: direction, duration: 1.2, ease: 'power2.out'});
			});
		}
	});
}
