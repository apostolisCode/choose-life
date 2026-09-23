<?php

defined( 'ABSPATH' ) or die();

/**
 * Journeys of hope: every donation as donor → hospital → patient, resolved to
 * named points with coordinates for the globe of templates/volunteer.php.
 *
 * Points without coordinates fall back to the country's label point from
 * data/countries.json (Natural Earth, public domain).
 */
class Inc_Journeys {

	const POST_TYPE = 'journey';
	const HOSPITAL_TAXONOMY = 'journey_hospital';

	// data/countries.json corrections for this audience
	const COUNTRY_OVERRIDES = [
		'CY' => [ 'continent' => 'Europe' ],
		'US' => [ 'name' => 'ΗΠΑ', 'name_en' => 'USA' ],
		'GB' => [ 'name_en' => 'UK' ],
	];

	private static $countries = null;

	/**
	 * ISO 3166-1 alpha-2 code → [name, name_en, continent, lat, lng]
	 */
	public static function countries() {
		if ( null === self::$countries ) {
			$file            = get_template_directory() . '/data/countries.json';
			$data            = file_exists( $file ) ? json_decode( file_get_contents( $file ), true ) : [];
			self::$countries = is_array( $data ) ? $data : [];

			foreach ( self::COUNTRY_OVERRIDES as $code => $override ) {
				if ( isset( self::$countries[ $code ] ) ) {
					self::$countries[ $code ] = array_merge( self::$countries[ $code ], $override );
				}
			}
		}

		return self::$countries;
	}

	public static function country( $code ) {
		$code = strtoupper( (string) $code );

		return self::countries()[ $code ] ?? null;
	}

	public static function country_name( $code ) {
		$country = self::country( $code );
		if ( ! $country ) {
			return '';
		}

		return strpos( get_locale(), 'el' ) === 0 ? $country['name'] : $country['name_en'];
	}

	public static function continent_label( $key ) {
		$labels = [
			'Europe'        => __( 'Europe', 'choose-life' ),
			'Asia'          => __( 'Asia', 'choose-life' ),
			'Africa'        => __( 'Africa', 'choose-life' ),
			'North America' => __( 'North America', 'choose-life' ),
			'South America' => __( 'South America', 'choose-life' ),
			'Oceania'       => __( 'Oceania', 'choose-life' ),
		];

		return $labels[ $key ] ?? $key;
	}

	/**
	 * A named point on the globe: the city (if any) in its country, at the
	 * given coordinates or at the country's label point.
	 */
	public static function place( $country_code, $city = '', $lat = null, $lng = null ) {
		$country = self::country( $country_code );
		if ( ! $country ) {
			return null;
		}

		$has_coords = is_numeric( $lat ) && is_numeric( $lng );
		$city       = trim( (string) $city );

		return [
			'country'      => strtoupper( $country_code ),
			'country_name' => self::country_name( $country_code ),
			'city'         => $city,
			'name'         => $city ?: self::country_name( $country_code ),
			'continent'    => $country['continent'],
			'lat'          => $has_coords ? (float) $lat : (float) $country['lat'],
			'lng'          => $has_coords ? (float) $lng : (float) $country['lng'],
		];
	}

	public static function hospital( $term ) {
		$term = $term instanceof WP_Term ? $term : get_term( (int) $term, self::HOSPITAL_TAXONOMY );
		if ( ! $term || is_wp_error( $term ) ) {
			return null;
		}

		$ref   = self::HOSPITAL_TAXONOMY . '_' . $term->term_id;
		$place = self::place(
			get_field( 'hospital_country', $ref ) ?: 'GR',
			get_field( 'hospital_city', $ref ),
			get_field( 'hospital_lat', $ref ),
			get_field( 'hospital_lng', $ref )
		);

		return $place ? array_merge( $place, [ 'id' => $term->term_id, 'hospital' => $term->name ] ) : null;
	}

	/**
	 * Every published journey, in the admin order (Intuitive CPO), ready for the globe.
	 */
	public static function get_all() {
		$posts = get_posts( [
			'post_type'        => self::POST_TYPE,
			'posts_per_page'   => - 1,
			'orderby'          => [ 'menu_order' => 'ASC', 'date' => 'ASC' ],
			'suppress_filters' => false,
		] );

		$journeys = [];
		foreach ( $posts as $post ) {
			$journey = self::format( $post );
			if ( $journey ) {
				$journeys[] = $journey;
			}
		}

		return $journeys;
	}

	private static function format( WP_Post $post ) {
		$donor = self::place(
			get_field( 'donor_country', $post->ID ) ?: 'GR',
			get_field( 'donor_city', $post->ID ),
			get_field( 'donor_lat', $post->ID ),
			get_field( 'donor_lng', $post->ID )
		);
		$patient = self::place(
			get_field( 'patient_country', $post->ID ),
			get_field( 'patient_city', $post->ID ),
			get_field( 'patient_lat', $post->ID ),
			get_field( 'patient_lng', $post->ID )
		);
		$hospital = self::hospital( get_field( 'hospital', $post->ID ) );

		// the sample leaves from the hospital; without one, from the donor's place
		if ( ! $patient || ! ( $hospital || $donor ) ) {
			return null;
		}

		$story = trim( (string) get_field( 'story', $post->ID ) );

		return [
			'id'       => $post->ID,
			'title'    => get_the_title( $post ),
			'story'    => $story ? wpautop( esc_html( $story ) ) : '',
			'donor'    => $donor,
			'hospital' => $hospital,
			'patient'  => $patient,
		];
	}

	/**
	 * Filter choices from the journeys: patient country and hospital.
	 */
	public static function filters( array $journeys ) {
		$countries = [];
		$hospitals = [];

		foreach ( $journeys as $journey ) {
			$countries[ $journey['patient']['country'] ] = $journey['patient']['country_name'];
			if ( $journey['hospital'] ) {
				$hospitals[ $journey['hospital']['id'] ] = $journey['hospital']['hospital'];
			}
		}

		$collator = class_exists( 'Collator' ) ? new Collator( get_locale() ) : null;
		$sort     = function ( array $items ) use ( $collator ) {
			$collator ? $collator->asort( $items ) : asort( $items );

			return $items;
		};

		return [
			'country'  => $sort( $countries ),
			'hospital' => $sort( $hospitals ),
		];
	}

	/**
	 * "Donor city → patient" summary of a journey for the list.
	 */
	public static function route_label( array $journey ) {
		$from = $journey['hospital'] ? $journey['hospital']['name'] : $journey['donor']['name'];

		return $from . ' → ' . $journey['patient']['name'];
	}
}
