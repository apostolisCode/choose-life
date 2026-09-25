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
// uStyle: 0 original (night Earth), 1 flat (beige land on a dark sea), 2 illustration (vector art, low-poly),
// 3 map (the grey map of the design, the countries of the chosen journey in red)
// uCountries: country ids (R, see country-ids.js), uActive: the ids of the chosen journey, faded by uActiveOn
export const earthFragment = /* glsl */ `
	uniform sampler2D uMask;
	uniform sampler2D uLights;
	uniform sampler2D uRelief;
	uniform sampler2D uDetailMask;
	uniform sampler2D uDetailLights;
	uniform vec4 uDetailBounds;
	uniform float uDetailOn;
	uniform vec3 uLightDir;
	uniform float uStyle;
	uniform sampler2D uCountries;
	uniform vec3 uActive;
	uniform float uActiveOn;

	varying vec2 vUv;
	varying vec3 vNormal;
	varying vec3 vView;

	float hash(vec2 p) {
		return fract(sin(dot(p, vec2(127.1, 311.7))) * 43758.5453);
	}

	float noise(vec2 p) {
		vec2 i = floor(p);
		vec2 f = fract(p);
		f = f * f * (3.0 - 2.0 * f);
		return mix(mix(hash(i), hash(i + vec2(1.0, 0.0)), f.x), mix(hash(i + vec2(0.0, 1.0)), hash(i + vec2(1.0, 1.0)), f.x), f.y);
	}

	// 1 where the country id of a texel is one of the chosen journey's (uActive)
	float chosen(ivec2 texel, ivec2 size) {
		texel.x = (texel.x + size.x) % size.x;
		texel.y = clamp(texel.y, 0, size.y - 1);
		float id = floor(texelFetch(uCountries, texel, 0).r * 255.0 + 0.5);
		vec3 hit = step(abs(vec3(id) - uActive), vec3(0.5)) * step(0.5, uActive);

		return max(max(hit.x, hit.y), hit.z);
	}

	// x: 1 where the texel is a chosen country, y: 1 where it is any country
	vec2 chosen2(ivec2 texel, ivec2 size) {
		return vec2(chosen(texel, size), step(0.5, floor(texelFetch(uCountries, ivec2((texel.x + size.x) % size.x, clamp(texel.y, 0, size.y - 1)), 0).r * 255.0 + 0.5)));
	}

	// the chosen countries, smooth: the 4 texels around interpolated (the id map itself
	// can't be filtered), as a share of the land of the id map. By the sea that share
	// stays 1 (the coast then comes from the sharper land mask); at a border with another
	// country it falls from 1 to 0, a smooth line instead of steps
	float chosenArea(vec2 uv) {
		ivec2 size = textureSize(uCountries, 0);
		vec2 st = uv * vec2(size) - 0.5;
		ivec2 i = ivec2(floor(st));
		vec2 f = fract(st);
		vec2 area = mix(
			mix(chosen2(i, size), chosen2(i + ivec2(1, 0), size), f.x),
			mix(chosen2(i + ivec2(0, 1), size), chosen2(i + ivec2(1, 1), size), f.x),
			f.y
		);

		return area.x / max(area.y, 1e-3);
	}

	// a random value per triangle of a (skewed) triangle grid over the map: low-poly facets
	float facet(vec2 p, float scale) {
		vec2 q = p * scale;
		q.x += q.y * 0.5;
		vec2 cell = floor(q);
		vec2 f = fract(q);
		return hash(cell + step(f.y, f.x) * vec2(0.37, 0.61));
	}

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

		// the brand palette: $c_dark #1C1C1C, $c_beige #EBE7E2, $c_offwhite #F8F4F3
		vec3 beige = vec3(0.922, 0.906, 0.886);
		vec3 offwhite = vec3(0.973, 0.957, 0.953);
		float sheen = pow(1.0 - facing, 7.0);
		// deserts only in the lower latitudes (the tundra is bright in the relief too)
		float dry = 1.0 - smoothstep(40.0, 50.0, abs(vUv.y - 0.5) * 180.0);

		if (uStyle > 2.5) {
			// map (colours of the design): #BFBFBF land on a #DFDFDF sea, #DFDFDF borders,
			// a darker edge; the countries of the chosen journey #F3223F with a white outline
			float light = smoothstep(0.3, 0.8, diffuse);
			vec3 sea = vec3(0.875) * (0.96 + 0.06 * light);
			vec3 ground = vec3(0.749) * (0.96 + 0.06 * light);
			color = mix(sea, ground, land);
			color = mix(color, vec3(0.875), outline * 0.9);

			// borders with the neighbours from the (interpolated) id map, the coasts from the
			// sharper land mask
			float area = chosenArea(vUv);
			float aa = fwidth(area) * 0.75 + 1e-4;
			float country = smoothstep(0.5 - aa, 0.5 + aa, area);
			color = mix(color, vec3(0.953, 0.133, 0.247) * (0.94 + 0.06 * light), country * land * uActiveOn);
			// white coasts and borders over them (the smooth lines of the mask, which also
			// hide the steps of the land mask when zoomed in)
			color = mix(color, vec3(1.0), outline * country * uActiveOn);

			color *= 0.86 + 0.14 * smoothstep(0.0, 0.35, facing);
		} else if (uStyle > 1.5) {
			// illustration: a vector-art Earth in low-poly facets. Sea in bands of blue (lighter by
			// the coasts), sandy land with leafy green where the forests are, white poles,
			// a few confetti flecks over the sea
			vec2 p = vUv * vec2(2.0, 1.0);
			float f = facet(p, 64.0);
			// light in 4 bands, the band edges jittered per triangle so they follow the facets
			float band = clamp(floor(diffuse * 4.0 + (f - 0.5) * 0.35), 0.0, 3.0) / 3.0;
			float tone = band + (f - 0.5) * 0.06;

			float lod = log2(float(textureSize(uMask, 0).x) / 512.0);
			float near = smoothstep(0.02, 0.2, textureLod(uMask, vUv, lod).r);
			vec3 sea = mix(vec3(0.13, 0.39, 0.62), vec3(0.38, 0.67, 0.85), tone);
			sea = mix(sea, vec3(0.55, 0.8, 0.92), near * (1.0 - land) * 0.45);

			vec3 sand = mix(vec3(0.82, 0.5, 0.24), vec3(0.95, 0.8, 0.53), tone);
			sand = mix(sand, vec3(0.97, 0.89, 0.72), (1.0 - smoothstep(0.55, 0.95, textureLod(uMask, vUv, lod - 1.0).r)) * 0.6);
			// forests: the dark parts of the relief, with jagged leafy edges and leaves inside
			float blobs = textureLod(uRelief, vUv, log2(float(textureSize(uRelief, 0).x) / 384.0)).r;
			float leafy = (noise(p * 140.0) - 0.5) * 0.05 + (noise(p * 30.0) - 0.5) * 0.06;
			float forest = max(1.0 - smoothstep(0.37, 0.41, blobs + leafy), 1.0 - dry);
			float leaves = step(0.55, noise(p * 260.0));
			vec3 green = mix(vec3(0.27, 0.5, 0.18), vec3(0.52, 0.74, 0.3), clamp(tone + leaves * 0.25, 0.0, 1.0));
			vec3 ground = mix(sand, green, forest);
			float lat = abs(vUv.y - 0.5) * 180.0 + (f - 0.5) * 3.0;
			ground = mix(ground, mix(vec3(0.84, 0.91, 0.95), vec3(1.0), tone), step(65.0, lat));

			color = mix(sea, ground, land);

			// confetti: a few small triangles of the palette over the sea
			float fleck = facet(p + 5.3, 230.0);
			vec3 confetti = fleck > 0.9985 ? vec3(0.95, 0.62, 0.3) : fleck > 0.997 ? vec3(0.52, 0.74, 0.3) : vec3(1.0);
			color = mix(color, confetti, step(0.9955, fleck) * (1.0 - near));

			// a darker edge, like the shadow side of the drawing
			color *= 0.82 + 0.18 * smoothstep(0.0, 0.3, facing);
		} else if (uStyle > 0.5) {
			// flat: beige land, drawn borders and coasts, a dark sea with a faint graticule
			vec3 sea = mix(vec3(0.12, 0.116, 0.112), vec3(0.2, 0.192, 0.184), diffuse);
			vec3 soil = beige * (0.72 + 0.22 * diffuse);
			color = mix(sea, soil, land);
			color += offwhite * max(line.x, line.y) * 0.035 * (1.0 - land);
			color = mix(color, vec3(0.6, 0.565, 0.53), outline * 0.65);
			color = mix(color, offwhite, sheen * 0.4);
		}

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
	uniform float uDashes;
	uniform float uFlat;
	uniform vec3 uColor;
	uniform vec3 uHeadColor;

	varying float vT;
	varying vec3 vNormal;
	varying vec3 vView;

	// uFlat: normal instead of additive blending (the flat / illustration looks), the
	// intensity goes to the alpha and the colour stays the arc colour
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
			base *= step(0.5, fract(vT * uDashes - uTime * 0.6 * uMotion));
			trail = 0.0;
			spark = 0.0;
		}

		vec3 color = uColor * (base + trail * (0.8 + uActive)) + uHeadColor * spark * (1.0 + uActive * 1.6);
		vec3 plain = mix(uColor, uHeadColor, clamp(spark * 2.0 + trail * 0.6, 0.0, 1.0));
		float alpha = (base + trail * 0.9 + spark) * profile * ends * uOpacity * (1.0 + uFlat * 0.8);
		gl_FragColor = vec4(mix(color, plain, uFlat), alpha);
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
	uniform float uFlat;
	uniform vec3 uColor;
	uniform vec3 uActiveColor;
	uniform vec3 uOutlineColor;

	varying float vActive;
	varying float vFacing;
	varying float vVisible;

	void main() {
		vec2 c = gl_PointCoord - 0.5;
		float r = length(c) * 2.0;
		float aa = fwidth(r);
		if (r > 1.0) discard;

		float disc = exp(-r * r * 18.0);
		float glow = exp(-r * 4.5) * 0.45;
		// expanding ring around the points of the active journey
		float wave = fract(uTime * 0.6);
		float ring = vActive * uMotion * smoothstep(0.08, 0.0, abs(r - wave)) * (1.0 - wave);

		vec3 color = mix(uColor, uActiveColor, vActive);
		float alpha = (disc + glow + ring) * smoothstep(0.0, 0.2, vFacing) * vVisible;

		// uFlat: a crisp dot in an outline, for normal blending
		float fill = 1.0 - smoothstep(0.4 - aa, 0.4 + aa, r);
		float edge = 1.0 - smoothstep(0.58 - aa, 0.58 + aa, r);
		vec3 plain = mix(uOutlineColor, color, max(fill, ring));
		float plainAlpha = max(edge, ring * 0.8) * smoothstep(0.0, 0.2, vFacing) * vVisible;

		gl_FragColor = vec4(mix(color * (1.0 + disc), plain, uFlat), mix(alpha, plainAlpha, uFlat));
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
	uniform float uFlat;
	uniform vec3 uColor;
	uniform vec3 uActiveColor;
	uniform vec3 uOutlineColor;

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
		// uFlat: no glow, a thin outline instead (normal blending)
		float outline = 1.0 - smoothstep(0.1, 0.14, d);
		float alpha = mix(fill + glow, outline, uFlat) * vAlpha;
		if (alpha < 0.003) discard;

		vec3 color = mix(uColor, uActiveColor, vActive);
		gl_FragColor = vec4(mix(color * (1.0 + fill * 0.6), mix(uOutlineColor, color, fill), uFlat), alpha);
	}
`;
