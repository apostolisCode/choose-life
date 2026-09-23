<?php
/**
 * Header Lang Switcher
 * Element
 *
 * Links to the other active WPML language(s), using
 * assets/svg/layout/flag-{code}.svg when available.
 */
if ( function_exists( 'icl_object_id' ) ) :
	$langs = apply_filters( 'wpml_active_languages', null, 'skip_missing=0&orderby=code' );
	$langs = array_filter( (array) $langs, function ( $lang ) {
		return ! $lang['active'];
	} );
	if ( count( $langs ) > 0 ) :
		?>
        <div class="lang-switcher">
			<?php
			foreach ( $langs as $lang ) {
				$flag = "assets/svg/layout/flag-{$lang['language_code']}.svg";
				$label = file_exists( get_template_directory() . "/$flag" )
					? sprintf( '<img src="%s" width="24.565" height="24.565" alt="%s"/>', esc_url( get_template_directory_uri() . "/$flag" ), esc_attr( $lang['translated_name'] ) )
					: CRL_Utils::get_upper( $lang['language_code'] );
				echo sprintf( '<a href="%s" title="%s" lang="%s" hreflang="%s" class="lang-switcher__link">%s</a>', esc_attr( $lang['url'] ), esc_attr( $lang['translated_name'] ), esc_attr( $lang['language_code'] ), esc_attr( $lang['language_code'] ), $label );
			}
			?>
        </div>
	<?php
	endif;
endif;
