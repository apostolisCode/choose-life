# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A WordPress installation for **Choose Life**, a donation platform. The site is served from a subdirectory (`/choose-life-donations/`) under WAMP on Windows. Almost all custom code lives in the theme at `wp-content/themes/choose-life/`; the WordPress core files in the repo root and the third-party plugins under `wp-content/plugins/` are stock and rarely edited.

Donations and recurring subscriptions are processed through the **Cardlink** (Greek bank) hosted payment gateway. The site is bilingual via WPML.

## Build & development

The front-end build lives in `wp-content/themes/choose-life/src/` (this is where `package.json` and `node_modules` are — **not** the theme root). Webpack compiles into the theme's `assets/` directory (`../assets` relative to `src/`).

```bash
cd wp-content/themes/choose-life/src
npm install              # requires Node 24.11.0 (see engines)
npm run build            # production build (minified)
npm start                # dev build, --watch, with BrowserSync live-reload
```

`npm start` proxies `http://localhost/choose-life-donations/` through BrowserSync and reloads on changes to compiled CSS and any `.php` file. There is no test suite or linter configured.

Webpack entry points (`src/webpack.config.js`) → output bundles in `assets/js/`:
- `main` — site-wide JS + SCSS (loads `main.scss`, lazysizes, the my-account-links Vue widget)
- `admin` — wp-admin assets
- `my-account` — the My Account Vue SPA
- `checkout` — the checkout/donation Vue SPA
- `donation` — the amount picker of the donation page template (`templates/donation.php`), reusing the checkout `DonationAmounts` component
- `vendors` — split chunk of `node_modules`

`jQuery` and `Vue` are treated as webpack externals (expected on `window`). SCSS partials in `src/assets/scss/config/`, the bootstrap functions/variables/mixins, and `helpers/mixins/_list.scss` are auto-injected into every `.scss` file via `sass-resources-loader` — don't re-import them.

## PHP architecture

`functions.php` is intentionally minimal — it requires `includes/post-types/index.php`, `template-hooks.php`, and `template-functions.php`. The real bootstrapping happens in `theme_setup()` (in `template-functions.php`, hooked to `after_setup_theme`), which includes the three autoloader files.

### Three class autoloaders (PSR-like, by prefix)

Classes are autoloaded by filename convention `class-{name}.php` where `{name}` is the lowercased class name (minus prefix) with `_` → `-`. Never manually `require` these; just instantiate.

| Prefix       | Directory       | Loader file                      | Example |
|--------------|-----------------|----------------------------------|---------|
| `Inc_`       | `includes/`     | `includes/includes-loader.php`   | `Inc_Api`, `Inc_Donation`, `Inc_Payment` |
| `CRL_`       | `library/` (+ `library/helpers/`) | `library/library-loader.php` | `CRL_Cache`, `CRL_Utils`, `CRL_Html`, `CRL_Svg` |
| `Component_` | `components/`   | `components/components-loader.php` | `Component_Button`, `Component_Element` |

Service classes under `includes/` follow a singleton pattern (`Inc_Foo::get_instance()`). They are wired up at the bottom of `template-hooks.php` on `after_setup_theme` / `acf/init` (e.g. `Inc_Api`, `Inc_Auth`, `Inc_Subscription`, `Inc_Admin`, `Inc_Window_Data`).

### The donation flow

1. **`Inc_Api`** exposes the front-end (Vue) endpoints as **admin-ajax actions** (`register_ajax_actions()`), prefixed `cl_` (e.g. `cl_place_order`, `cl_get_donation`, `cl_pay_donation`, `cl_update_user`, `cl_user_donations`, `cl_user_subscriptions`, `cl_reset_password*`). It also keeps **two REST routes** under `api/v1` — `payment` and `donation/recurring` — which are the Cardlink gateway callbacks (browser redirect / server-to-server), verified by HMAC digest, not nonce/cookie.
2. **`Inc_Donation`** creates `donations` / `subscriptions` custom posts (registered in `includes/post-types/index.php`; both are non-public, admin-only, no front-end create).
3. **`Inc_Payment`** builds the Cardlink hosted-form params (HMAC-signed with `payment_secret`), supports one-off and recurring (`extRecurring*`) donations, and handles the gateway's redirect callback. Test vs. production endpoint is chosen by the `enable_test_environment` ACF option.

### Authentication (admin-ajax + WP cookie + nonce)

Auth is WordPress-native cookie session, **not** JWT (the old `jwt-auth` / `jwt-whitelist` plugins were removed). **`Inc_Auth`** is the auth/session ajax handler exposing `cl_login` (`wp_signon`), `cl_register` (creates user via `Inc_User::create_user`, then auto-logs-in via `wp_set_auth_cookie`), `cl_me` (session check, replaces token validation), and `cl_logout`. Every ajax action verifies a nonce (`check_ajax_referer('cl_ajax', '_ajax_nonce')`).

- The front-end posts `application/x-www-form-urlencoded` with `action`, `_ajax_nonce`, and `payload` (a JSON string, decoded server-side via `get_payload()`). Handlers emit results with `wp_send_json($response)` keeping the legacy `{success, statusCode, code, message, data}` shape.
- A fresh `cl_ajax` nonce is localized into `window.urls.nonce` on every page load ([template-functions.php](wp-content/themes/choose-life/template-functions.php) `theme_scripts_localize()`). After an in-SPA login/register the handler returns a **new nonce** (the `set_logged_in_cookie` hook in `Inc_Auth` makes `wp_create_nonce` use the new session token); the JS keeps it in-memory (`currentNonce` in [api/index.js](wp-content/themes/choose-life/src/assets/js/scripts/vue/api/index.js)). No nonce is persisted client-side.

### Configuration via ACF

Settings are ACF options-page fields (`get_field( '...', 'options' )`), not constants: e.g. `payment_mid`, `payment_secret`, `enable_test_environment`, `checkout_page_url`, `my_account_url`. The options page ("Theme Options") is registered in `acf_init_options_page()`. ACF field group definitions are version-controlled as JSON in `acf-json/` (local JSON sync is enabled — editing a field group in wp-admin writes a new JSON file here, and vice versa).

## Vue front-end

Two Vue 3 SPAs (Pinia + vue-router, hash-mode) live in `src/assets/js/scripts/vue/`:
- **`checkout/`** — donation checkout flow (Start → Login/Register → Payment → Complete).
- **`my-account/`** — logged-in account area (Account, Donations, Reset Password).

They share `vue/api/index.js` (axios wrapper; `axiosPublic` vs `axiosPrivate` which injects the JWT `Authorization` header), `vue/stores/` (`user`, `ui`), and `vue/helpers/`. The login / register / reset-password UI is shared too, in `vue/shared/auth/` (`AuthLayout`, `LoginForm`, `RegisterForm`, `ResetPassword`, `OrDivider`); each app's pages are thin wrappers that set the texts, the redirect route and the extra links (checkout adds the stepper and "continue as guest"). They mount only on the matching page templates (`templates/checkout.php`, `templates/my-account.php`) — see `theme_scripts()` for the conditional enqueue.

### PHP → JS data bridge

- `theme_scripts_localize()` exposes `window.urls` (home, theme, assets, ajax, **rest**, privacy).
- `Inc_Window_Data::print_object()` prints `window.app_config` in the footer with translated UI `strings`, `my_account_url`, and (on checkout/my-account templates) the ACF `countries_list`. Add user-facing translatable strings to `get_strings()` rather than hardcoding them in Vue.

All translatable strings use the `'choose-life'` text domain.

## Journeys globe (volunteer page)

`templates/volunteer.php` shows the `journey` posts (donor → hospital → patient; hospitals are the `journey_hospital` taxonomy with coordinates as ACF term fields) on a WebGL globe. `Inc_Journeys` resolves every point to a name + lat/lng, falling back to the country's label point from `data/countries.json` (Natural Earth, public domain) when no coordinates are set. The list and details panel are server-rendered (`elements/sections/journeys-globe.php`); `scripts/journeys/index.js` adds filters and the tour, and lazy-loads `scripts/journeys/globe.js` (three.js, its own async `three` chunk — never in `vendors`) near the viewport.

- Textures in `src/assets/img/globe/` are pre-baked equirectangular images (mask = R land / G borders+coasts, city lights, relief) from NASA Black Marble / Blue Marble and Natural Earth; 2k load first, 4k on large screens, and the `earth-detail-*` pair (Europe/Mediterranean, lng −12…48, lat 28…62 — `DETAIL_BOUNDS` in `globe.js`) only when a route zooms there.
- Looks (`THEMES` in `globe.js`, `uStyle` in `shaders.js`, `.journeys--{look}` in `_volunteer.scss`): `original`, `flat`, `illustration` (the default, `$default_theme` in `journeys-globe.php`) and `map`. `?globe=` picks one for anyone; editors get a selector on the card. `map` fills the countries of the chosen journey from `countries-4k.png` (Natural Earth 1:50m, country id in R, grown a few px into the sea, loaded with nearest filtering) via the ISO2 table `country-ids.js`.
- In wp-admin, the journey / hospital lat-lng fields get an OpenStreetMap picker (`scripts/admin/coords-picker.js`, Leaflet copied to `assets/vendor/leaflet` by webpack and enqueued only on those screens).

## Conventions & gotchas

- The site runs in a **subdirectory**; `.htaccess` uses `RewriteBase /choose-life-donations/`. Hardcoded `installationUrl` in `webpack.config.js` also assumes this path.
- `wp-config.php`, `.htaccess`, `wp-content/uploads/`, and debug logs are git-ignored. The theme's `src/node_modules/` and the built `assets/` are git-ignored but live on disk (they show up in searches) — ignore them when grepping; scope searches to `wp-content/themes/choose-life/` excluding `src/node_modules`.
- WPML: post type / taxonomy translation settings are in the theme's `wpml-config.xml` (donations and subscriptions are not translatable; `faq` and `faq_category` are). ACF fields carry their WPML preference in `acf-json` (`wpml_cf_preferences`: 0 ignore, 1 copy, 2 translate, 3 copy once) and each group has an ACFML mode (`acfml_field_group_mode`): `translation` uses ACFML's per-type defaults (text/textarea/wysiwyg/url/link translate, the rest copy), `advanced` (Expert) where some fields must not follow them — e.g. the Cardlink credentials, contact email/phone and social URLs are copied, never sent for translation. Give new fields a preference when adding them.
- Contact Form 7 forms get their markup from theme files `elements/cf7-*.php` (plugin "Contact Form 7: Template Support", header comment `CF7-Template: <name>`, form type "Template" in the CF7 editor). Field names there must match the mail tags in the form's Mail tab. CF7 auto-`<p>` is disabled in `template-hooks.php`.
- Use `write_log( $data )` (defined in `template-functions.php`) for debugging — it writes to `wp-content/site-debug.log`.
- `template-hooks.php` deliberately strips WP defaults: oEmbed, XML-RPC, emojis, generator version, query-string versions on assets, and block-library CSS.
