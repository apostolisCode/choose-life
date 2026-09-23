<?php

namespace WPML\DataSharing;

use WPML\OutboundLinks\OutboundLinks;

class DataSharingSection {

	const SECTION_ID = 'ml-content-setup-sec-reporting';

	const RENDER_ACTION = 'otgs_installer_render_local_components_setting';

	public static function isAvailable() {
		return (bool) has_action( self::RENDER_ACTION );
	}

	public static function heading() {
		return __( 'Get a proactive support', 'sitepress' );
	}

	public static function args() {
		return array(
			'plugin_name'                => 'WPML',
			'plugin_site'                => 'wpml.org',
			'plugin_repository'          => 'wpml',
			'use_styles'                 => true,
			'use_radio'                  => true,
			'custom_heading'             => '',
			'privacy_policy_url'         => OutboundLinks::to(
				'https://wpml.org/documentation/privacy-policy-and-gdpr-compliance/',
				array(
					'medium'   => 'settings',
					'campaign' => 'data-sharing',
				)
			),
			'custom_description'         => esc_html__(
				'WPML can send information about your site’s plugins, theme and content stats to wpml.org. 
						This allows our support team to help you much faster and to contact you about potential problems and their solutions.',
				'sitepress'
			),
			'sharing_data_details_text'  => esc_html__(
				'Full details of the info we’re proposing to share',
				'sitepress'
			),
			'sharing_data_details_url'   => OutboundLinks::to(
				'https://wpml.org/documentation/privacy-policy-and-gdpr-compliance/optional-data-sharing/',
				array(
					'medium'   => 'settings',
					'campaign' => 'data-sharing',
				)
			),
			'custom_radio_label_yes'     => esc_html__(
				'Yes, send this information to wpml.org to improve my site’s maintenance and support',
				'sitepress'
			),
			'custom_radio_label_no'      => esc_html__(
				'No, don\'t send this information and skip maintenance alerts',
				'sitepress'
			),
		);
	}

	public static function render( $heading_tag = 'h3' ) {
		$heading_tag = 'h2' === $heading_tag ? 'h2' : 'h3';
		?>
		<div class="wpml-section wpml-section-wpml-theme-and-plugins-reporting" id="<?php echo esc_attr( self::SECTION_ID ); ?>">
			<div class="wpml-section-header">
				<?php if ( 'h2' === $heading_tag ) : ?>
					<h2><?php echo esc_html( self::heading() ); ?></h2>
				<?php else : ?>
					<h3><?php echo esc_html( self::heading() ); ?></h3>
				<?php endif; ?>
			</div>
			<div class="wpml-section-content">
				<?php do_action( self::RENDER_ACTION, self::args() ); ?>
			</div>
		</div><!-- #ml-content-setup-sec-reporting -->
		<?php
	}
}
