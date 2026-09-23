/**
 * WebGL globe of the journeys of hope (three.js, loaded on demand).
 *
 * The globe turns under a fixed camera: yaw (about the Earth's axis) and
 * pitch (tilt), both eased towards a target. Picking a journey turns the
 * globe so that its arc faces the viewer and zooms by the arc's length.
 * Each journey is a donor → hospital leg (dashed, shown when active) and a
 * hospital → patient arc along which the sample travels.
 */
import {
	AdditiveBlending,
	BackSide,
	BufferGeometry,
	CatmullRomCurve3,
	Color,
	Float32BufferAttribute,
	Group,
	ImageBitmapLoader,
	LinearMipmapLinearFilter,
	Mesh,
	MeshBasicMaterial,
	NoColorSpace,
	PerspectiveCamera,
	Points,
	Raycaster,
	Scene,
	ShaderMaterial,
	SphereGeometry,
	Texture,
	TextureLoader,
	TubeGeometry,
	Vector2,
	Vector3,
	Vector4,
	WebGLRenderer,
} from 'three';
import {arcPoints, nearestAngle, rotationFor, toVector} from './geo';
import {
	arcFragment,
	arcVertex,
	atmosphereFragment,
	atmosphereVertex,
	earthFragment,
	earthVertex,
	heartFragment,
	heartVertex,
	pointFragment,
	pointVertex,
} from './shaders';

const FOV = 30;
const DEG = Math.PI / 180;
const ARC_COLOR = new Color('#bfe2ff');
const ARC_ACTIVE_COLOR = new Color('#ffffff');
const HEAD_COLOR = new Color('#9fd4ff');
const HEAD_ACTIVE_COLOR = new Color('#ff5a5f');
const HEART_COLOR = new Color('#d6ecff');
const HEART_ACTIVE_COLOR = new Color('#ff3b44');
const POINT_COLOR = new Color('#d8ecff');
const POINT_ACTIVE_COLOR = new Color('#ffffff');
const DONOR_COLOR = new Color('#8fc2ff');
const SPEED = 0.16;
// region of the detail textures (earth-detail-*): lng −12…48, lat 28…62, as UV (u = (lng+180)/360, v = (lat+90)/180)
const DETAIL_BOUNDS = [168 / 360, 118 / 180, 228 / 360, 152 / 180];
const inDetailRegion = (lat, lng) => lng > -12 && lng < 48 && lat > 28 && lat < 62;
const ACTIVE_SPEED = 0.2;

const ease = (current, target, rate, dt) => current + (target - current) * (1 - Math.exp(-rate * dt));
const placeKey = (place) => `${place.lat.toFixed(2)},${place.lng.toFixed(2)}`;

export default class JourneyGlobe {
	/**
	 * @param {HTMLElement} stage   element the canvas fills
	 * @param {HTMLElement} overlay element for the HTML labels / sample marker
	 * @param {object} options      journeys, textures (base URL), reducedMotion, onSelect(id), onInteract(), onReady()
	 */
	constructor(stage, overlay, options) {
		this.stage = stage;
		this.overlay = overlay;
		this.options = options;
		this.motion = options.reducedMotion ? 0 : 1;

		this.state = {
			yaw: -110 * DEG, pitch: -8 * DEG, zoom: 1,
			targetYaw: -110 * DEG, targetPitch: -8 * DEG, targetZoom: 1,
			velocityYaw: 0, velocityPitch: 0,
		};
		this.activeId = null;
		this.hoverId = null;
		this.visible = new Set(options.journeys.map((j) => j.id));
		this.inView = true;
		this.running = false;
		this.time = 0;
		this.last = 0;

		this.setupRenderer();
		this.setupScene();
		this.setupArcs();
		this.setupPoints();
		this.setupOverlay();
		this.setupPointer();
		this.resize();

		this.resizeObserver = new ResizeObserver(() => this.resize());
		this.resizeObserver.observe(stage);

		this.loadTextures().then(() => {
			this.start();
			options.onReady?.();
		});
	}

	// ─── setup ──────────────────────────────────────────────────────────

	setupRenderer() {
		this.renderer = new WebGLRenderer({antialias: true, alpha: true, powerPreference: 'high-performance'});
		this.renderer.setClearColor(0x000000, 0);
		this.pixelRatio = Math.min(window.devicePixelRatio || 1, 2);
		this.renderer.setPixelRatio(this.pixelRatio);
		this.canvas = this.renderer.domElement;
		this.canvas.className = 'journeys__canvas';
		this.stage.prepend(this.canvas);
	}

	setupScene() {
		this.scene = new Scene();
		this.camera = new PerspectiveCamera(FOV, 1, 0.01, 50);
		this.camera.position.set(0, 0, 3);

		this.globe = new Group();
		this.scene.add(this.globe);

		this.earthUniforms = {
			uMask: {value: null},
			uLights: {value: null},
			uRelief: {value: null},
			uDetailMask: {value: null},
			uDetailLights: {value: null},
			uDetailBounds: {value: new Vector4(...DETAIL_BOUNDS)},
			uDetailOn: {value: 0},
			uLightDir: {value: new Vector3(-0.6, 0.55, 0.9)},
		};
		this.earth = new Mesh(
			new SphereGeometry(1, 128, 64),
			new ShaderMaterial({vertexShader: earthVertex, fragmentShader: earthFragment, uniforms: this.earthUniforms})
		);
		this.earth.visible = false;
		this.globe.add(this.earth);

		// the atmosphere does not turn with the globe
		this.atmosphere = new Mesh(
			new SphereGeometry(1.16, 64, 32),
			new ShaderMaterial({
				vertexShader: atmosphereVertex,
				fragmentShader: atmosphereFragment,
				uniforms: {uColor: {value: new Color('#4f9dff')}, uStrength: {value: 1.6}},
				side: BackSide,
				blending: AdditiveBlending,
				transparent: true,
				depthWrite: false,
			})
		);
		this.scene.add(this.atmosphere);
	}

	arcMaterial(offset, dashed = false) {
		return new ShaderMaterial({
			vertexShader: arcVertex,
			fragmentShader: arcFragment,
			uniforms: {
				uTime: {value: 0},
				uHead: {value: offset * 1.4 - 0.2},
				uOpacity: {value: dashed ? 0 : 1},
				uActive: {value: 0},
				uHover: {value: 0},
				uMotion: {value: this.motion},
				uSoft: {value: 0},
				uDashed: {value: dashed ? 1 : 0},
				uColor: {value: (dashed ? DONOR_COLOR : ARC_COLOR).clone()},
				uHeadColor: {value: HEAD_COLOR.clone()},
			},
			blending: AdditiveBlending,
			transparent: true,
			depthWrite: false,
		});
	}

	tube(points, angle, radiusScale) {
		const curve = new CatmullRomCurve3(points);
		const radius = Math.min(0.0032, Math.max(0.0009, angle * 0.06)) * radiusScale;
		const segments = Math.max(32, Math.round(angle * 140));
		const radial = 8;
		const geometry = new TubeGeometry(curve, segments, radius, radial, false);

		// taper to a point at both ends, so the tube never shows its open end
		const position = geometry.getAttribute('position');
		const center = new Vector3();
		const vertex = new Vector3();
		for (let i = 0; i <= segments; i++) {
			const t = i / segments;
			const taper = Math.sin(Math.min(1, Math.min(t, 1 - t) / 0.08) * Math.PI / 2);
			curve.getPointAt(t, center);
			for (let j = 0; j <= radial; j++) {
				const k = i * (radial + 1) + j;
				vertex.fromBufferAttribute(position, k).sub(center).multiplyScalar(taper).add(center);
				position.setXYZ(k, vertex.x, vertex.y, vertex.z);
			}
		}
		position.needsUpdate = true;

		return {curve, geometry};
	}

	setupArcs() {
		this.arcs = new Map();
		this.pickMeshes = [];
		const pickMaterial = new MeshBasicMaterial();

		this.options.journeys.forEach((journey, index) => {
			const start = journey.hospital || journey.donor;
			const a = toVector(start.lat, start.lng);
			const b = toVector(journey.patient.lat, journey.patient.lng);
			const {points, angle} = arcPoints(a, b);
			const offset = (index * 0.618) % 1;
			const arc = {journey, angle, offset, phase: offset, a, b, meshes: [], materials: [], curve: null, opacity: 1, active: 0, hover: 0};

			if (angle > 0.002) {
				const core = this.tube(points, angle, 1);
				const glow = this.tube(points, angle, 3.4);
				const coreMaterial = this.arcMaterial(offset);
				const glowMaterial = this.arcMaterial(offset);
				glowMaterial.uniforms.uSoft.value = 1;
				arc.curve = core.curve;
				arc.meshes.push(new Mesh(core.geometry, coreMaterial), new Mesh(glow.geometry, glowMaterial));
				arc.materials.push(coreMaterial, glowMaterial);

				// fat invisible tube for picking
				const pick = new Mesh(new TubeGeometry(core.curve, 24, Math.max(0.012, angle * 0.02), 5, false), pickMaterial);
				pick.visible = false;
				pick.userData.journeyId = journey.id;
				this.pickMeshes.push(pick);
				this.globe.add(pick);
			}

			// donor → hospital (dashed, only for the active journey)
			if (journey.hospital && journey.donor) {
				const d = toVector(journey.donor.lat, journey.donor.lng);
				const leg = arcPoints(d, a, 24);
				if (leg.angle > 0.002) {
					const tube = this.tube(leg.points, leg.angle, 0.8);
					const material = this.arcMaterial(0, true);
					arc.donorMesh = new Mesh(tube.geometry, material);
					arc.donorMaterial = material;
					this.globe.add(arc.donorMesh);
				}
			}

			arc.meshes.forEach((mesh) => this.globe.add(mesh));
			this.arcs.set(journey.id, arc);
		});
	}

	setupPoints() {
		// one point per place (hospitals and patients), donors only as part of the active journey
		const places = new Map();
		const add = (place, kind, journeyId) => {
			const key = placeKey(place);
			if (!places.has(key)) {
				places.set(key, {place, kind, journeys: new Set()});
			}
			places.get(key).journeys.add(journeyId);
			if (kind !== 'donor') {
				places.get(key).kind = kind === 'hospital' || places.get(key).kind === 'hospital' ? 'hospital' : kind;
			}
		};
		this.options.journeys.forEach((j) => {
			if (j.hospital) add(j.hospital, 'hospital', j.id);
			add(j.patient, 'patient', j.id);
			if (j.donor) add(j.donor, 'donor', j.id);
		});
		this.places = [...places.values()];

		const positions = [];
		const sizes = [];
		this.places.forEach(({place, kind}) => {
			toVector(place.lat, place.lng, 1.004).toArray(positions, positions.length);
			sizes.push(kind === 'hospital' ? 13 : kind === 'patient' ? 11 : 8);
		});

		const geometry = new BufferGeometry();
		geometry.setAttribute('position', new Float32BufferAttribute(positions, 3));
		geometry.setAttribute('aSize', new Float32BufferAttribute(sizes, 1));
		geometry.setAttribute('aActive', new Float32BufferAttribute(new Array(this.places.length).fill(0), 1));
		geometry.setAttribute('aVisible', new Float32BufferAttribute(new Array(this.places.length).fill(0), 1));

		this.pointUniforms = {
			uTime: {value: 0},
			uMotion: {value: this.motion},
			uPixelRatio: {value: this.pixelRatio},
			uColor: {value: POINT_COLOR},
			uActiveColor: {value: POINT_ACTIVE_COLOR},
		};
		this.points = new Points(geometry, new ShaderMaterial({
			vertexShader: pointVertex,
			fragmentShader: pointFragment,
			uniforms: this.pointUniforms,
			blending: AdditiveBlending,
			transparent: true,
			depthWrite: false,
		}));
		this.globe.add(this.points);
		this.updatePoints();
		this.setupHearts();
	}

	setupHearts() {
		this.heartArcs = [...this.arcs.values()].filter((arc) => arc.curve);
		const count = this.heartArcs.length;
		const geometry = new BufferGeometry();
		geometry.setAttribute('position', new Float32BufferAttribute(new Float32Array(count * 3), 3));
		geometry.setAttribute('aSize', new Float32BufferAttribute(new Float32Array(count).fill(20), 1));
		geometry.setAttribute('aActive', new Float32BufferAttribute(new Float32Array(count), 1));
		geometry.setAttribute('aAlpha', new Float32BufferAttribute(new Float32Array(count), 1));

		this.hearts = new Points(geometry, new ShaderMaterial({
			vertexShader: heartVertex,
			fragmentShader: heartFragment,
			uniforms: {
				uPixelRatio: {value: this.pixelRatio},
				uColor: {value: HEART_COLOR},
				uActiveColor: {value: HEART_ACTIVE_COLOR},
			},
			blending: AdditiveBlending,
			transparent: true,
			depthWrite: false,
		}));
		this.hearts.frustumCulled = false;
		this.globe.add(this.hearts);
	}

	updateHearts() {
		const geometry = this.hearts.geometry;
		const position = geometry.getAttribute('position');
		const size = geometry.getAttribute('aSize');
		const active = geometry.getAttribute('aActive');
		const alpha = geometry.getAttribute('aAlpha');
		const point = new Vector3();

		this.heartArcs.forEach((arc, i) => {
			// reduced motion: a still heart in the middle of the arc
			const head = this.motion ? arc.phase * 1.4 - 0.2 : 0.5;
			const t = Math.min(1, Math.max(0, head));
			arc.curve.getPointAt(t, point);
			position.setXYZ(i, point.x, point.y, point.z);
			const fade = Math.min(1, Math.max(0, Math.min(head, 1 - head) / 0.06));
			// the active arc carries the sample marker instead of a heart
			alpha.setX(i, fade * arc.opacity * 0.85 * (1 - arc.active * this.motion));
			active.setX(i, arc.active);
			size.setX(i, 20 + 6 * arc.hover);
		});
		[position, size, active, alpha].forEach((attribute) => (attribute.needsUpdate = true));
	}

	setupOverlay() {
		this.labels = [];
		this.marker = document.createElement('span');
		this.marker.className = 'journeys__marker';
		this.marker.innerHTML = `<img src="${this.options.icons.sample}" width="17" height="24" alt="">`;
		this.overlay.appendChild(this.marker);
	}

	setupPointer() {
		const canvas = this.canvas;
		const raycaster = new Raycaster();
		const ndc = new Vector2();
		let drag = null;

		const pick = (event) => {
			const rect = canvas.getBoundingClientRect();
			ndc.set(((event.clientX - rect.left) / rect.width) * 2 - 1, -((event.clientY - rect.top) / rect.height) * 2 + 1);
			raycaster.setFromCamera(ndc, this.camera);
			const earthHit = raycaster.intersectObject(this.earth)[0];
			const hits = raycaster.intersectObjects(this.pickMeshes.filter((m) => this.visible.has(m.userData.journeyId)));
			// only arcs in front of the Earth
			const hit = hits.find((h) => !earthHit || h.distance < earthHit.distance + 0.01);

			return hit ? hit.object.userData.journeyId : null;
		};

		canvas.addEventListener('pointerdown', (event) => {
			drag = {x: event.clientX, y: event.clientY, moved: 0, touch: event.pointerType === 'touch', t: performance.now()};
			this.state.velocityYaw = this.state.velocityPitch = 0;
			this.dragging = true;
			if (!drag.touch) canvas.setPointerCapture(event.pointerId);
		});

		canvas.addEventListener('pointermove', (event) => {
			if (drag) {
				const dx = event.clientX - drag.x;
				const dy = event.clientY - drag.y;
				drag.moved += Math.abs(dx) + Math.abs(dy);
				drag.x = event.clientX;
				drag.y = event.clientY;
				const now = performance.now();
				const dt = Math.max(1, now - drag.t) / 1000;
				drag.t = now;

				const perPixel = 1.6 / (this.radiusPx * this.state.zoom);
				this.state.targetYaw += dx * perPixel;
				this.state.velocityYaw = (dx * perPixel) / dt;
				if (!drag.touch) {
					this.state.targetPitch = Math.max(-1.1, Math.min(1.1, this.state.targetPitch + dy * perPixel));
					this.state.velocityPitch = (dy * perPixel) / dt;
				}
				if (drag.moved > 6) {
					this.userInteracted();
				}
				return;
			}

			if (event.pointerType === 'mouse') {
				const id = pick(event);
				if (id !== this.hoverId) {
					this.hoverId = id;
					canvas.style.cursor = id ? 'pointer' : '';
				}
			}
		});

		const end = (event) => {
			if (!drag) return;
			const click = drag.moved < 6;
			drag = null;
			this.dragging = false;
			if (click && event.type === 'pointerup') {
				const id = pick(event);
				if (id) {
					this.userInteracted();
					this.options.onSelect?.(id);
				}
			}
		};
		canvas.addEventListener('pointerup', end);
		canvas.addEventListener('pointercancel', end);
		canvas.addEventListener('pointerleave', () => {
			if (!drag && this.hoverId) {
				this.hoverId = null;
				canvas.style.cursor = '';
			}
		});
	}

	userInteracted() {
		if (!this.interacted) {
			this.interacted = true;
			this.options.onInteract?.();
		}
	}

	// a texture from an image decoded off the main thread (ImageBitmap) when possible
	texture(file) {
		const url = this.options.textures + file;
		const finish = (image, flipY) => {
			const texture = new Texture(image);
			texture.flipY = flipY;
			texture.colorSpace = NoColorSpace;
			texture.anisotropy = this.renderer.capabilities.getMaxAnisotropy();
			texture.minFilter = LinearMipmapLinearFilter;
			texture.needsUpdate = true;

			return texture;
		};

		return new Promise((resolve) => {
			if (typeof createImageBitmap === 'function') {
				const loader = new ImageBitmapLoader();
				// bitmaps can't be flipped on upload: flip while decoding
				loader.setOptions({imageOrientation: 'flipY', premultiplyAlpha: 'none', colorSpaceConversion: 'none'});
				loader.load(url, (bitmap) => resolve(finish(bitmap, false)), undefined, () => resolve(null));
			} else {
				new TextureLoader().load(url, (texture) => resolve(finish(texture.image, true)), undefined, () => resolve(null));
			}
		});
	}

	swap(uniform, texture) {
		if (!texture) return;
		this.earthUniforms[uniform].value?.dispose();
		this.earthUniforms[uniform].value = texture;
	}

	/**
	 * Progressive: the 2k textures first (the globe shows up quickly), then on
	 * large screens the 4k ones; the detail textures only when a route zooms
	 * into their region (loadDetail()).
	 */
	loadTextures() {
		const gl = this.renderer.getContext();
		const big = gl.getParameter(gl.MAX_TEXTURE_SIZE) >= 4096 && this.stage.clientWidth * this.pixelRatio >= 1400;

		return Promise.all([
			this.texture('earth-mask-2k.png'),
			this.texture('earth-lights-2k.jpg'),
			this.texture('earth-relief-2k.jpg'),
		]).then(([mask, lights, relief]) => {
			this.swap('uMask', mask);
			this.swap('uLights', lights);
			this.swap('uRelief', relief);
			this.earth.visible = true;

			if (big) {
				Promise.all([this.texture('earth-mask-4k.png'), this.texture('earth-lights-4k.jpg')]).then(([mask4k, lights4k]) => {
					this.swap('uMask', mask4k);
					this.swap('uLights', lights4k);
				});
			}
		});
	}

	loadDetail() {
		if (this.detailRequested) return;
		this.detailRequested = true;

		Promise.all([this.texture('earth-detail-mask.png'), this.texture('earth-detail-lights.jpg')]).then(([mask, lights]) => {
			if (!mask || !lights) return;
			this.swap('uDetailMask', mask);
			this.swap('uDetailLights', lights);
			this.detailTarget = 1;
		});
	}

	// ─── layout ─────────────────────────────────────────────────────────

	resize() {
		const w = this.stage.clientWidth;
		const h = this.stage.clientHeight;
		if (!w || !h) return;

		this.width = w;
		this.height = h;
		this.renderer.setSize(w, h, false);

		// desktop: a big globe rising from the bottom, like the design; smaller screens: centred
		const wide = w >= 1000;
		this.center = wide ? {x: w * 0.47, y: h * 0.79} : {x: w * 0.5, y: h * 0.56};
		this.radiusPx = wide ? Math.min(h * 0.58, w * 0.33) : Math.min(w * 0.4, h * 0.42);
		this.focusY = wide ? h * 0.42 : h * 0.4;

		this.camera.aspect = w / h;
		this.camera.setViewOffset(w, h, w / 2 - this.center.x, h / 2 - this.center.y, w, h);
		this.updateCamera(true);
		// keep the active route in place when the layout changes
		if (this.activeId && this.arcs) this.select(this.activeId);

		if (!this.running) this.render();
	}

	// camera distance that shows the globe with radius radiusPx · zoom
	distanceFor(zoom) {
		const tanHalf = Math.tan((FOV / 2) * DEG);
		const alpha = Math.atan((this.radiusPx * zoom) / (this.height / 2) * tanHalf);

		return Math.max(1.06, 1 / Math.sin(alpha));
	}

	updateCamera(force = false) {
		const distance = this.distanceFor(this.state.zoom);
		if (force || Math.abs(this.camera.position.z - distance) > 1e-5) {
			this.camera.position.set(0, 0, distance);
			// leave room for the highest arcs (up to 1.42 from the centre) in front of the Earth
			this.camera.near = Math.max(0.01, distance - 1.6);
			this.camera.updateProjectionMatrix();
		}
	}

	// latitude (in the camera frame) of the point of the sphere at the focus spot,
	// for the camera distance of the given zoom (a closer camera sees it higher up)
	computeFocusLat(zoom = 1) {
		const raycaster = new Raycaster();
		const ndc = new Vector2(((this.center.x / this.width) * 2 - 1), -((this.focusY / this.height) * 2 - 1));
		const saved = this.camera.position.z;
		this.camera.position.z = this.distanceFor(zoom);
		this.camera.updateMatrixWorld();
		raycaster.setFromCamera(ndc, this.camera);
		const ray = raycaster.ray;
		// ray / unit sphere intersection
		const b = ray.origin.dot(ray.direction);
		const c = ray.origin.lengthSq() - 1;
		const disc = b * b - c;
		this.camera.position.z = saved;
		this.camera.updateMatrixWorld();
		if (disc < 0) return 20;
		const hit = ray.origin.clone().addScaledVector(ray.direction, -b - Math.sqrt(disc));

		return Math.asin(hit.y) / DEG;
	}

	// ─── journeys ───────────────────────────────────────────────────────

	select(id) {
		this.activeId = id;
		const arc = this.arcs.get(id);
		if (!arc) {
			this.clearSelection();
			return;
		}

		// aim at the middle of the whole route (donor, hospital, patient)
		const journey = arc.journey;
		const stops = [journey.donor, journey.hospital, journey.patient].filter(Boolean);
		const mid = stops.reduce((sum, p) => sum.add(toVector(p.lat, p.lng)), new Vector3());
		if (arc.curve) mid.add(arc.curve.getPointAt(0.5).normalize().multiplyScalar(stops.length));
		mid.normalize();

		const lat = Math.asin(mid.y) / DEG;
		const lng = Math.atan2(-mid.z, mid.x) / DEG;
		// zoom by the spread of the route, then turn it to the focus spot of that zoom
		const spread = stops.reduce((max, p) => Math.max(max, toVector(p.lat, p.lng).angleTo(mid)), 0);
		this.state.targetZoom = Math.max(1, Math.min(3.4, 0.42 / Math.max(spread, 0.05)));
		if (this.state.targetZoom > 1.3 && inDetailRegion(lat, lng)) {
			this.loadDetail();
		}

		const {yaw, pitch} = rotationFor(lat, lng, this.computeFocusLat(this.state.targetZoom));
		this.state.targetYaw = nearestAngle(yaw, this.state.yaw);
		this.state.targetPitch = Math.max(-1.1, Math.min(1.1, pitch));

		this.updatePoints();
		this.buildLabels(journey);
		if (!this.running) this.render();
	}

	clearSelection() {
		this.activeId = null;
		this.state.targetZoom = 1;
		this.updatePoints();
		this.buildLabels(null);
	}

	setVisible(ids) {
		this.visible = new Set(ids);
		this.updatePoints();
	}

	updatePoints() {
		const active = this.arcs.get(this.activeId)?.journey;
		const activeKeys = new Set(active ? [active.donor, active.hospital, active.patient].filter(Boolean).map(placeKey) : []);
		const attrActive = this.points.geometry.getAttribute('aActive');
		const attrVisible = this.points.geometry.getAttribute('aVisible');

		this.places.forEach((entry, i) => {
			const isActive = activeKeys.has(placeKey(entry.place));
			const shown = [...entry.journeys].some((id) => this.visible.has(id));
			attrActive.setX(i, isActive ? 1 : 0);
			attrVisible.setX(i, isActive || (shown && entry.kind !== 'donor') ? 1 : 0);
		});
		attrActive.needsUpdate = true;
		attrVisible.needsUpdate = true;
	}

	buildLabels(journey) {
		this.labels.forEach((label) => label.el.remove());
		this.labels = [];
		if (!journey) return;

		const seen = new Set();
		[[journey.donor, 'donor'], [journey.hospital, 'hospital'], [journey.patient, 'patient']].forEach(([place, kind]) => {
			if (!place || seen.has(placeKey(place))) return;
			seen.add(placeKey(place));
			const el = document.createElement('span');
			el.className = `journeys__label journeys__label--${kind}`;
			el.textContent = place.name;
			this.overlay.appendChild(el);
			this.labels.push({el, position: toVector(place.lat, place.lng, 1.005)});
		});
	}

	setInView(inView) {
		this.inView = inView;
		if (inView) this.start();
	}

	// ─── loop ───────────────────────────────────────────────────────────

	start() {
		if (this.running || !this.earth.visible) return;
		this.running = true;
		this.last = performance.now();
		const tick = (now) => {
			if (!this.running) return;
			if (!this.inView || document.hidden) {
				this.running = false;
				return;
			}
			const dt = Math.min(0.05, (now - this.last) / 1000);
			this.last = now;
			this.update(dt);
			this.render();
			this.frame = requestAnimationFrame(tick);
		};
		this.frame = requestAnimationFrame(tick);
	}

	update(dt) {
		const s = this.state;
		this.time += dt * (this.motion ? 1 : 0);

		// idle spin, then inertia after a drag
		if (!this.activeId && this.motion && !this.dragging) {
			s.targetYaw += dt * 0.045;
		}
		if (Math.abs(s.velocityYaw) > 1e-4 || Math.abs(s.velocityPitch) > 1e-4) {
			s.targetYaw += s.velocityYaw * dt * 0.35;
			s.targetPitch = Math.max(-1.1, Math.min(1.1, s.targetPitch + s.velocityPitch * dt * 0.35));
			s.velocityYaw *= Math.exp(-dt * 5);
			s.velocityPitch *= Math.exp(-dt * 5);
		}

		const rate = this.motion ? 3.2 : 60;
		s.yaw = ease(s.yaw, s.targetYaw, rate, dt);
		s.pitch = ease(s.pitch, s.targetPitch, rate, dt);
		s.zoom = ease(s.zoom, s.targetZoom, this.motion ? 2.4 : 60, dt);
		this.globe.rotation.set(s.pitch, s.yaw, 0, 'XYZ');
		this.updateCamera();

		// arcs: filters, active / hover highlight
		this.arcs.forEach((arc, id) => {
			const isActive = id === this.activeId;
			arc.opacity = ease(arc.opacity, this.visible.has(id) ? (this.activeId && !isActive ? 0.45 : 1) : 0, 6, dt);
			arc.active = ease(arc.active, isActive ? 1 : 0, 6, dt);
			arc.hover = ease(arc.hover, id === this.hoverId ? 1 : 0, 10, dt);
			arc.phase = (arc.phase + dt * this.motion * (isActive ? ACTIVE_SPEED : SPEED)) % 1;
			arc.materials.forEach((m) => {
				const u = m.uniforms;
				u.uTime.value = this.time;
				u.uHead.value = arc.phase * 1.4 - 0.2;
				u.uOpacity.value = arc.opacity;
				u.uActive.value = arc.active;
				u.uHover.value = arc.hover;
				u.uColor.value.copy(ARC_COLOR).lerp(ARC_ACTIVE_COLOR, arc.active);
				u.uHeadColor.value.copy(HEAD_COLOR).lerp(HEAD_ACTIVE_COLOR, arc.active);
			});
			arc.meshes.forEach((mesh) => (mesh.visible = arc.opacity > 0.01));
			if (arc.donorMaterial) {
				arc.donorMaterial.uniforms.uTime.value = this.time;
				arc.donorMaterial.uniforms.uOpacity.value = arc.active;
				arc.donorMesh.visible = arc.active > 0.01;
			}
		});
		this.pointUniforms.uTime.value = this.time;
		this.updateHearts();
		if (this.detailTarget) {
			const u = this.earthUniforms.uDetailOn;
			u.value = this.motion ? ease(u.value, 1, 3, dt) : 1;
		}

		this.updateOverlay();
	}

	// project a globe-space point; hidden when it is behind the Earth
	project(position, out) {
		const world = position.clone().applyMatrix4(this.globe.matrixWorld);
		const toCamera = this.camera.position.clone().sub(world);
		out.front = world.clone().normalize().dot(toCamera.normalize());
		world.project(this.camera);
		out.x = (world.x * 0.5 + 0.5) * this.width;
		out.y = (-world.y * 0.5 + 0.5) * this.height;

		return out;
	}

	updateOverlay() {
		this.globe.updateMatrixWorld();
		const p = {};

		this.labels.forEach(({el, position}) => {
			this.project(position, p);
			el.style.transform = `translate3d(${p.x.toFixed(1)}px, ${p.y.toFixed(1)}px, 0) translate(-50%, -150%)`;
			el.style.opacity = String(Math.max(0, Math.min(1, (p.front - 0.05) * 6)));
		});

		// the sample, riding the head of the active arc
		const arc = this.arcs.get(this.activeId);
		let visible = false;
		if (arc && arc.curve && this.motion) {
			const head = arc.phase * 1.4 - 0.2;
			if (head > 0 && head < 1) {
				this.project(arc.curve.getPointAt(head), p);
				if (p.front > 0.02) {
					visible = true;
					this.marker.style.transform = `translate3d(${p.x.toFixed(1)}px, ${p.y.toFixed(1)}px, 0) translate(-50%, -50%)`;
				}
			}
		}
		this.marker.classList.toggle('is-visible', visible);
	}

	render() {
		this.renderer.render(this.scene, this.camera);
	}

	dispose() {
		this.running = false;
		cancelAnimationFrame(this.frame);
		this.resizeObserver.disconnect();
		this.renderer.dispose();
	}
}
