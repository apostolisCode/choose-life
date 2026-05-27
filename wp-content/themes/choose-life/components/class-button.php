<?php
/* 
if ( $button = get_field('about_button') ) { 
	Component_Button::render(array(
		'field' => $button,
		'class' => 'btn btn--primary',
	));
} 
 */

class Component_Button extends Component_Base {

	protected static $params = [
		'field'     => null, // acf field object. ex. get_field(key)
		'hide_text' => false,
		'class'     => null,
		'wrapper_class' => null,
		'data_attr' => null
	];

	public static function get_render( $params = [] ) {
		$options = wp_parse_args( $params, self::$params );
		ob_start();

		$clone = $options['field'];
		if ( $clone ):
			$type          = isset( $clone['type'] ) ? $clone['type'] : 'internal';
			$btn_name      = isset( $clone['button_name'] ) ? $clone['button_name'] : null;
			$internal_url  = isset( $clone['internal_url'] ) ? $clone['internal_url'] : null;
			$external_link = isset( $clone['external_url'] ) ? $clone['external_url'] : null;
			$anchor_filter = isset( $clone['anchor_filter'] ) ? $clone['anchor_filter'] : null;
			$section_id = isset( $clone['section_id'] ) ? $clone['section_id'] : null;
			$popup_form = isset( $clone['form'] ) ? $clone['form'] : null;
			$class         = $options['class'];
			$wrapper_class = $options['wrapper_class'];
			$data_attr = $options['data_attr'];
			$hide_text = $options['hide_text'];
			$link_url  = '';
			switch ( $type ) {
				case 'external':
					$link_url = $external_link;
					break;
				case 'internal':
					$link_url = $anchor_filter ? $internal_url.$anchor_filter : $internal_url;
					break;
				case 'scroll':
					$link_url = '#';
					break;
				case 'popup':
					$link_url = '#';
					break;	
				
			}

			$attrs = [
				'href'  => $link_url,
				'class' => $class,
				'title' => $btn_name
			];
			if ( $type == 'external' || $type == 'file' ) {
				$attrs['target'] = '_blank';
			}
			if ( $type == 'external' ) {
				$attrs['rel'] = 'nofollow';
			}

			if ( $type == 'scroll' ) {
				$attrs['data-scroll-to'] = $section_id;
			}

			if ( $type == 'popup' ) {

				if($popup_form) {
					$attrs['data-trigger-popup-form'] = $popup_form->ID;
				}

			}

			$tags = '';
			foreach ( $attrs as $tag => $value ) {
				$tags .= ' ' . $tag . '="' . $value . '"';
			}

			if ( $btn_name || $link_url || $section_id) :
				if ( $wrapper_class) { ?>
					<div class="<?php echo $wrapper_class; ?>"<?php echo $data_attr ? ' ' . $data_attr : ''; ?>>
				<?php } ?>
					<a <?php echo $tags; ?>>
						<?php echo !$hide_text ? $btn_name : null; ?>
					</a>
				<?php if ( $wrapper_class) { ?>
					</div>
				<?php } ?>
			<?php endif;
		endif;

		return ob_get_clean();
	}

}
