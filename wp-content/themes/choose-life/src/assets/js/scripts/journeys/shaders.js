/**
 * GLSL of the journeys globe. Colours are written as display (sRGB) values;
 * the materials skip three.js' colour-space conversion.
 */

export const earthVertex = /* glsl */ `
	varying vec2 vUv;
	varying vec3 vNormal;
	varying vec3 vView;

	void main() {
		vUv = uv;
		vec4 world = modelMatrix * vec4(position, 1.0);
		vNormal = normalize(mat3(modelMatrix) * normal);
		vView = normalize(cameraPosition - world.xyz);
		gl_Position = projectionMatrix * viewMatrix * world;
	}
`;

// mask: R land, G country outlines · lights: city lights (Black Marble) · relief: Blue Marble luminance
// uDetail*: sharper mask / lights for the region where the globe zooms (bounds in UV space)
export const earthFragment = /* glsl */ `
	uniform sampler2D uMask;
	uniform sampler2D uLights;
	uniform sampler2D uRelief;
	uniform sampler2D uDetailMask;
	uniform sampler2D uDetailLights;
	uniform vec4 uDetailBounds;
	uniform float uDetailOn;
	uniform vec3 uLightDir;

	varying vec2 vUv;
	varying vec3 vNormal;
	varying vec3 vView;

	void main() {
		vec3 n = normalize(vNormal);
		vec3 v = normalize(vView);
		float facing = clamp(dot(n, v), 0.0, 1.0);
		float diffuse = clamp(dot(n, normalize(uLightDir)) * 0.5 + 0.5, 0.0, 1.0);

		vec4 mask = texture2D(uMask, vUv);
		float lights = texture2D(uLights, vUv).r;
		float halo = texture2D(uLights, vUv, 3.0).r;

		// inside the detail region, with a soft seam
		vec2 duv = (vUv - uDetailBounds.xy) / (uDetailBounds.zw - uDetailBounds.xy);
		float edge = min(min(duv.x, 1.0 - duv.x), min(duv.y, 1.0 - duv.y));
		float detail = uDetailOn * smoothstep(0.0, 0.03, edge);
		// sampled unconditionally: mip selection needs uniform control flow
		vec2 cuv = clamp(duv, 0.0, 1.0);
		mask = mix(mask, texture2D(uDetailMask, cuv), detail);
		lights = mix(lights, texture2D(uDetailLights, cuv).r, detail);
		halo = mix(halo, texture2D(uDetailLights, cuv, 4.5).r, detail);

		float land = mask.r;
		float outline = mask.g;
		float relief = texture2D(uRelief, vUv).r;

		// ocean: vivid blue, lighter over shallow water and towards the light
		vec3 ocean = mix(vec3(0.015, 0.075, 0.33), vec3(0.09, 0.33, 0.92), clamp(diffuse * 0.75 + relief * 0.9 - 0.15, 0.0, 1.0));
		// land: deep navy with a little relief
		vec3 ground = mix(vec3(0.018, 0.04, 0.13), vec3(0.075, 0.13, 0.30), relief) * (0.6 + 0.55 * diffuse);
		vec3 color = mix(ocean, ground, land);

		// faint 10° graticule over the sea
		vec2 cell = vUv * vec2(36.0, 18.0);
		vec2 dist = abs(fract(cell) - 0.5);
		vec2 width = fwidth(cell) * 1.2;
		vec2 line = 1.0 - smoothstep(vec2(0.0), width, 0.5 - dist);
		color += vec3(0.35, 0.6, 1.0) * max(line.x, line.y) * 0.05 * (1.0 - land);

		// borders and coasts
		color += vec3(0.4, 0.62, 1.0) * outline * 0.28;

		// city lights with a soft halo (a blurrier mip level)
		lights = clamp((lights - 0.1) / 0.9, 0.0, 1.0);
		halo = clamp((halo - 0.1) / 0.9, 0.0, 1.0);
		color += vec3(1.0, 0.74, 0.38) * pow(lights, 1.5) * 1.9 * land;
		color += vec3(1.0, 0.55, 0.25) * halo * 0.55 * land;

		// limb: atmosphere tint towards the edge
		float rim = pow(1.0 - facing, 2.6);
		color = mix(color, vec3(0.32, 0.68, 1.0), rim * 0.7);
		color += vec3(0.2, 0.5, 1.0) * rim * 0.45;

		gl_FragColor = vec4(color, 1.0);
	}
`;

export const atmosphereVertex = /* glsl */ `
	varying vec3 vNormal;

	void main() {
		vNormal = normalize(normalMatrix * normal);
		gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
	}
`;

export const atmosphereFragment = /* glsl */ `
	uniform vec3 uColor;
	uniform float uStrength;

	varying vec3 vNormal;

	void main() {
		float intensity = pow(clamp(0.74 - dot(vNormal, vec3(0.0, 0.0, 1.0)), 0.0, 1.0), 3.2) * uStrength;
		gl_FragColor = vec4(uColor * intensity, intensity);
	}
`;

// arcs are tubes; uv.x runs along the arc (arc-length), the view angle of the
// tube normal gives a bright core with soft edges
export const arcVertex = /* glsl */ `
	varying float vT;
	varying vec3 vNormal;
	varying vec3 vView;

	void main() {
		vT = uv.x;
		vec4 view = modelViewMatrix * vec4(position, 1.0);
		vNormal = normalize(normalMatrix * normal);
		vView = normalize(-view.xyz);
		gl_Position = projectionMatrix * view;
	}
`;

export const arcFragment = /* glsl */ `
	uniform float uTime;
	uniform float uHead;
	uniform float uOpacity;
	uniform float uActive;
	uniform float uHover;
	uniform float uMotion;
	uniform float uSoft;
	uniform float uDashed;
	uniform vec3 uColor;
	uniform vec3 uHeadColor;

	varying float vT;
	varying vec3 vNormal;
	varying vec3 vView;

	void main() {
		float core = abs(dot(normalize(vNormal), normalize(vView)));
		float profile = mix(pow(core, 0.7), pow(core, 3.0), uSoft);
		float ends = smoothstep(0.0, 0.04, vT) * smoothstep(1.0, 0.96, vT);

		// light trail behind the travelling heart (uHead, set per arc; runs past both ends)
		float d = uHead - vT;
		float trail = d >= 0.0 ? exp(-d * 7.0) * (1.0 - smoothstep(0.35, 0.6, d)) : 0.0;
		float spark = exp(-abs(d) * 40.0) * 0.5;
		trail *= uMotion;
		spark *= uMotion;

		float base = 0.26 + 0.34 * uActive + 0.3 * uHover;
		if (uDashed > 0.5) {
			base *= step(0.5, fract(vT * 14.0 - uTime * 0.6 * uMotion));
			trail = 0.0;
			spark = 0.0;
		}

		vec3 color = uColor * (base + trail * (0.8 + uActive)) + uHeadColor * spark * (1.0 + uActive * 1.6);
		float alpha = (base + trail * 0.9 + spark) * profile * ends * uOpacity;
		gl_FragColor = vec4(color, alpha);
	}
`;

export const pointVertex = /* glsl */ `
	attribute float aSize;
	attribute float aActive;
	attribute float aVisible;

	uniform float uPixelRatio;

	varying float vActive;
	varying float vFacing;
	varying float vVisible;

	void main() {
		vec4 world = modelMatrix * vec4(position, 1.0);
		vFacing = dot(normalize(world.xyz), normalize(cameraPosition - world.xyz));
		vActive = aActive;
		vVisible = aVisible;
		gl_PointSize = aSize * (1.0 + aActive * 1.4) * uPixelRatio;
		gl_Position = projectionMatrix * viewMatrix * world;
	}
`;

export const pointFragment = /* glsl */ `
	uniform float uTime;
	uniform float uMotion;
	uniform vec3 uColor;
	uniform vec3 uActiveColor;

	varying float vActive;
	varying float vFacing;
	varying float vVisible;

	void main() {
		vec2 c = gl_PointCoord - 0.5;
		float r = length(c) * 2.0;
		if (r > 1.0) discard;

		float disc = exp(-r * r * 18.0);
		float glow = exp(-r * 4.5) * 0.45;
		// expanding ring around the points of the active journey
		float wave = fract(uTime * 0.6);
		float ring = vActive * uMotion * smoothstep(0.08, 0.0, abs(r - wave)) * (1.0 - wave);

		vec3 color = mix(uColor, uActiveColor, vActive);
		float alpha = (disc + glow + ring) * smoothstep(0.0, 0.2, vFacing) * vVisible;
		gl_FragColor = vec4(color * (1.0 + disc), alpha);
	}
`;

// the sample travelling on each arc: a glowing heart (signed distance, crisp at any size)
export const heartVertex = /* glsl */ `
	attribute float aSize;
	attribute float aActive;
	attribute float aAlpha;

	uniform float uPixelRatio;

	varying float vActive;
	varying float vAlpha;

	void main() {
		vActive = aActive;
		vAlpha = aAlpha;
		gl_PointSize = aSize * uPixelRatio;
		gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
	}
`;

export const heartFragment = /* glsl */ `
	uniform vec3 uColor;
	uniform vec3 uActiveColor;

	varying float vActive;
	varying float vAlpha;

	float dot2(vec2 v) {
		return dot(v, v);
	}

	// Inigo Quilez, heart SDF (tip at the origin, top at y ≈ 1)
	float sdHeart(vec2 p) {
		p.x = abs(p.x);
		if (p.y + p.x > 1.0) {
			return sqrt(dot2(p - vec2(0.25, 0.75))) - sqrt(2.0) / 4.0;
		}
		return sqrt(min(dot2(p - vec2(0.0, 1.0)), dot2(p - 0.5 * max(p.x + p.y, 0.0)))) * sign(p.x - p.y);
	}

	void main() {
		// point sprite → heart space, centred, with room for the glow
		vec2 p = (vec2(gl_PointCoord.x, 1.0 - gl_PointCoord.y) - 0.5) * 2.4 + vec2(0.0, 0.52);
		float d = sdHeart(p);
		float fill = 1.0 - smoothstep(-0.02, 0.02, d);
		float glow = exp(-max(d, 0.0) * 9.0) * 0.55;
		float alpha = (fill + glow) * vAlpha;
		if (alpha < 0.003) discard;

		vec3 color = mix(uColor, uActiveColor, vActive);
		gl_FragColor = vec4(color * (1.0 + fill * 0.6), alpha);
	}
`;
