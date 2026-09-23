/**
 * Geometry helpers for the journeys globe (unit sphere, three.js axes).
 *
 * lat/lng map onto SphereGeometry's default UVs, so an equirectangular
 * texture lines up: x = cos(lat)·cos(lng), y = sin(lat), z = −cos(lat)·sin(lng).
 */
import {Vector3} from 'three';

const DEG = Math.PI / 180;

export function toVector(lat, lng, radius = 1, target = new Vector3()) {
	const phi = lat * DEG;
	const lambda = lng * DEG;

	return target.set(
		Math.cos(phi) * Math.cos(lambda) * radius,
		Math.sin(phi) * radius,
		-Math.cos(phi) * Math.sin(lambda) * radius
	);
}

export function toLatLng(vector) {
	const v = vector.clone().normalize();

	return {
		lat: Math.asin(v.y) / DEG,
		lng: Math.atan2(-v.z, v.x) / DEG,
	};
}

/**
 * Points of a great-circle arc from a to b (unit vectors), lifted off the
 * surface in proportion to its length.
 */
export function arcPoints(a, b, steps = 48) {
	const angle = a.angleTo(b);
	const lift = Math.min(0.42, Math.max(0.012, angle * 0.28));
	const sin = Math.sin(angle) || 1;
	const points = [];

	for (let i = 0; i <= steps; i++) {
		const t = i / steps;
		const p = angle < 1e-6
			? a.clone()
			: a.clone().multiplyScalar(Math.sin((1 - t) * angle) / sin).add(b.clone().multiplyScalar(Math.sin(t * angle) / sin));
		points.push(p.normalize().multiplyScalar(1.002 + lift * Math.sin(Math.PI * t)));
	}

	return {points, angle};
}

/**
 * Globe rotation (pitch about X, then yaw about Y, Euler order 'XYZ') that
 * brings the point lat/lng to the world direction of latitude focusLat on the
 * front meridian (the one facing the camera).
 */
export function rotationFor(lat, lng, focusLat) {
	return {
		yaw: (-90 - lng) * DEG,
		pitch: (lat - focusLat) * DEG,
	};
}

// the equivalent of angle (±2π·k) closest to reference, so the globe takes the short way
export function nearestAngle(angle, reference) {
	const turn = Math.PI * 2;

	return angle + Math.round((reference - angle) / turn) * turn;
}
