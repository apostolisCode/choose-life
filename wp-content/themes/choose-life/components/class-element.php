<?php

class Component_Element extends Component_Base {

	public static function get_render( $params = [] ) {

		ob_start();

		if ( isset( $params['element'] ) ) {

			$elementFile = locate_template( 'elements/' . $params['element'] . ( preg_match( '/\.php$/i', $params['element'] ) ? '' : '.php' ) );

			if ( !empty( $elementFile ) ) {
				require( $elementFile );
			}
		}
		return ob_end_clean();
	}

}
