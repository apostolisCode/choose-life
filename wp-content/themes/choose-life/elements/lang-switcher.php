<?php
/**
 * Header Lang Switcher
 * Element
 *
 */
if ( function_exists( 'icl_object_id' ) ) :
	$langs = apply_filters( 'wpml_active_languages', null, 'skip_missing=0&orderby=code' );
	usort( $langs, function ( $a, $b ) {
		if ( $a['active'] === $b['active'] ) {
			return 0;
		}

		return ( $a['active'] > $b['active'] ) ? - 1 : 1;
	} );
	$active_lang = array_shift( $langs );
	if ( count( $langs ) > 0 ) :
		?>
        <div class="lang">
			<?php
			foreach ( $langs as $lang ) {
				echo sprintf( '<a href="%s" title="%s" lang="%s" hreflang="%s">%s</a>', esc_attr( $lang['url'] ), esc_attr( $lang['translated_name'] ), esc_attr( $lang['language_code'] ), esc_attr( $lang['language_code'] ), CRL_Utils::get_upper( $lang['language_code'] ) );
			}
			?>
        </div>
	<?php
	endif;
endif;