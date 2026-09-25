<?php
/**
 * Journeys of hope on an interactive globe (scripts/journeys/).
 *
 * The list and the details panel are plain HTML, so they work without
 * JavaScript / WebGL; the script adds the globe, the filters and the tour.
 *
 * @var array $args {
 * @type array $journeys Inc_Journeys::get_all()
 * }
 */

$journeys = $args['journeys'] ?? [];
if ( ! $journeys ) {
	return;
}

$svg     = get_template_directory_uri() . '/assets/svg/volunteer/';
$filters = Inc_Journeys::filters( $journeys );
$first   = $journeys[0];

$place_label = function ( $place ) {
	return $place['city'] ? $place['city'] . ', ' . $place['country_name'] : $place['country_name'];
};

// look of the globe (illustration by default), while the design is being chosen: ?globe=original|flat|map for anyone, a selector for editors
$themes = [
	'original'     => __( 'Original', 'choose-life' ),
	'flat'         => __( 'Flat', 'choose-life' ),
	'illustration' => __( 'Illustration', 'choose-life' ),
	'map'          => __( 'Map', 'choose-life' ),
];
$default_theme = 'illustration';
$theme         = sanitize_key( wp_unslash( $_GET['globe'] ?? '' ) );
$theme         = isset( $themes[ $theme ] ) ? $theme : $default_theme;
?>
<section class="journeys journeys--<?php echo esc_attr( $theme ); ?>" data-journeys aria-label="<?php esc_attr_e( 'Journeys of hope', 'choose-life' ); ?>">
    <div class="journeys__card" data-tilt-card data-tilt="-5">
        <span class="journeys__card-back" data-tilt-card-back aria-hidden="true"></span>

		<?php if ( current_user_can( 'edit_posts' ) ) : ?>
            <div class="journeys__themes" role="group" aria-label="<?php esc_attr_e( 'Globe look', 'choose-life' ); ?>">
				<?php foreach ( $themes as $key => $label ) : ?>
                    <button type="button" class="journeys__theme" data-journeys-theme="<?php echo esc_attr( $key ); ?>"
                            aria-pressed="<?php echo $key === $theme ? 'true' : 'false'; ?>"><?php echo esc_html( $label ); ?></button>
				<?php endforeach; ?>
            </div>
		<?php endif; ?>

        <div class="journeys__stage" data-journeys-stage aria-hidden="true">
            <img class="journeys__fallback" src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/globe/globe-fallback.webp' ); ?>"
                 width="1381" height="1139" alt="" loading="lazy" decoding="async" data-journeys-fallback/>
            <div class="journeys__overlay" data-journeys-overlay></div>
            <p class="journeys__hint" data-journeys-hint hidden><?php esc_html_e( 'Drag to turn the Earth, pick a journey to follow it', 'choose-life' ); ?></p>
        </div>

        <article class="journeys__panel" data-journeys-panel aria-live="polite">
            <button type="button" class="journeys__close" data-journeys-close aria-label="<?php esc_attr_e( 'Close', 'choose-life' ); ?>">
                <img src="<?php echo esc_url( $svg . 'close.svg' ); ?>" width="14" height="14" alt=""/>
            </button>
            <h2 class="journeys__panel-title" data-field="title"><?php echo esc_html( $first['title'] ); ?></h2>
            <dl class="journeys__facts">
                <div>
                    <dt><?php esc_html_e( 'Donor from', 'choose-life' ); ?></dt>
                    <dd data-field="donor"><?php echo esc_html( $place_label( $first['donor'] ) ); ?></dd>
                </div>
                <div data-field-row="hospital"<?php echo $first['hospital'] ? '' : ' hidden'; ?>>
                    <dt><?php esc_html_e( 'Hospital of donation', 'choose-life' ); ?></dt>
                    <dd data-field="hospital"><?php echo $first['hospital'] ? esc_html( $first['hospital']['hospital'] . ', ' . $first['hospital']['name'] ) : ''; ?></dd>
                </div>
                <div>
                    <dt><?php esc_html_e( 'Transplant to a patient in', 'choose-life' ); ?></dt>
                    <dd data-field="patient"><?php echo esc_html( $place_label( $first['patient'] ) ); ?></dd>
                </div>
            </dl>
            <div class="journeys__story" data-field="story"><?php echo wp_kses_post( $first['story'] ); ?></div>

            <div class="journeys__route">
                <svg class="journeys__route-arc" viewBox="0 0 252 34" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M0.64 32.5C86 -7.97 161.66 -9.69 250.64 32.5" stroke="#fff" stroke-opacity=".5" stroke-width="3" stroke-dasharray="6 6" fill="none"/>
                </svg>
                <span class="journeys__sample" aria-hidden="true">
                    <img src="<?php echo esc_url( $svg . 'sample.svg' ); ?>" width="17" height="24" alt=""/>
                </span>
                <span class="journeys__place">
                    <img src="<?php echo esc_url( $svg . 'map-pin.svg' ); ?>" width="18" height="21" alt=""/>
                    <span data-field="from"><?php echo esc_html( $first['hospital'] ? $first['hospital']['name'] : $first['donor']['name'] ); ?></span>
                </span>
                <span class="visually-hidden">→</span>
                <span class="journeys__place">
                    <img src="<?php echo esc_url( $svg . 'map-pin.svg' ); ?>" width="18" height="21" alt=""/>
                    <span data-field="to"><?php echo esc_html( $first['patient']['name'] ); ?></span>
                </span>
            </div>
        </article>

        <div class="journeys__side">
            <div class="journeys__filters-bar">
                <button type="button" class="journeys__filters-toggle" aria-expanded="false" aria-controls="journeys-filters" data-journeys-filters-toggle hidden>
                    <img src="<?php echo esc_url( $svg . 'filters.svg' ); ?>" width="16" height="16" alt=""/>
                    <?php esc_html_e( 'Filters', 'choose-life' ); ?>
                    <span class="journeys__filters-count" data-journeys-filters-count hidden></span>
                </button>
            </div>

            <div class="journeys__filters" id="journeys-filters" data-journeys-filters data-lenis-prevent hidden>
				<?php
				$groups = [
					'country'  => __( 'Patient country', 'choose-life' ),
					'hospital' => __( 'Hospital of donation', 'choose-life' ),
				];
				foreach ( $groups as $key => $label ) :
					if ( count( $filters[ $key ] ) < 2 ) {
						continue;
					}
					?>
                    <fieldset class="journeys__filter-group">
                        <legend><?php echo esc_html( $label ); ?></legend>
						<?php foreach ( $filters[ $key ] as $value => $name ) : ?>
                            <label class="journeys__chip">
                                <input type="checkbox" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
                                <span><?php echo esc_html( $name ); ?></span>
                            </label>
						<?php endforeach; ?>
                    </fieldset>
				<?php endforeach; ?>
                <button type="button" class="journeys__filters-clear" data-journeys-filters-clear><?php esc_html_e( 'Clear filters', 'choose-life' ); ?></button>
            </div>

            <div class="journeys__list-wrap">
                <ul class="journeys__list" data-journeys-list data-lenis-prevent>
					<?php foreach ( $journeys as $i => $journey ) : ?>
                        <li data-journey-item="<?php echo (int) $journey['id']; ?>"
                            data-country="<?php echo esc_attr( $journey['patient']['country'] ); ?>"
                            data-hospital="<?php echo esc_attr( $journey['hospital']['id'] ?? '' ); ?>">
                            <button type="button" class="journey-card<?php echo $i === 0 ? ' is-active' : ''; ?>" data-journey="<?php echo (int) $journey['id']; ?>"
                                    aria-pressed="<?php echo $i === 0 ? 'true' : 'false'; ?>">
                                <span class="journey-card__title"><?php echo esc_html( $journey['title'] ); ?></span>
                                <span class="journey-card__route"><?php echo esc_html( Inc_Journeys::route_label( $journey ) ); ?></span>
                            </button>
                        </li>
					<?php endforeach; ?>
                </ul>
                <p class="journeys__empty" data-journeys-empty hidden><?php esc_html_e( 'No journeys match these filters.', 'choose-life' ); ?></p>
                <span class="journeys__scrollbar" aria-hidden="true"><span class="journeys__scrollbar-thumb" data-journeys-thumb></span></span>
            </div>
        </div>
    </div>
</section>
<script>
    var journeys_globe = <?php echo wp_json_encode( [
		'journeys' => $journeys,
		'textures' => get_template_directory_uri() . '/assets/img/globe/',
		'icons'    => [ 'sample' => $svg . 'sample.svg' ],
		'theme'    => $theme,
		'default'  => $default_theme,
	] ); ?>;
</script>
