<?php
$theme_url = get_template_directory_uri() . '/assets/';
$cta_title = get_field( 'footer_cta_title', 'options' );
$cta_link  = get_field( 'footer_cta_link', 'options' );
$contact   = [
	'title'   => get_field( 'contact_title', 'options' ),
	'email'   => get_field( 'contact_email', 'options' ),
	'phone'   => get_field( 'contact_phone', 'options' ),
	'address' => get_field( 'contact_address', 'options' ),
];
$socials   = array_filter( [
	'facebook'  => [ get_field( 'social_facebook', 'options' ), 'Facebook', 23.9139, 50 ],
	'instagram' => [ get_field( 'social_instagram', 'options' ), 'Instagram', 44.7502, 44 ],
	'linkedin'  => [ get_field( 'social_linkedin', 'options' ), 'LinkedIn', 43.6481, 43.9999 ],
], function ( $social ) {
	return ! empty( $social[0] );
} );
$menus     = array_filter( [ 'footer-menu', 'footer-menu-info' ], 'has_nav_menu' );
?>
<footer class="site-footer">
	<?php if ( $cta_title || ! empty( $cta_link['url'] ) ) : ?>
        <div class="site-footer__cta">
			<?php if ( $cta_title ) : ?>
                <p class="site-footer__cta-title"><?php echo esc_html( $cta_title ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $cta_link['url'] ) ) : ?>
                <a href="<?php echo esc_url( $cta_link['url'] ); ?>" class="cl-btn cl-btn--primary cl-btn--lg"<?php echo $cta_link['target'] ? ' target="' . esc_attr( $cta_link['target'] ) . '" rel="noopener"' : ''; ?>><?php echo esc_html( $cta_link['title'] ); ?></a>
			<?php endif; ?>
        </div>
	<?php endif; ?>

    <div class="site-footer__main">
        <div class="site-footer__inner">
            <div class="site-footer__top">
                <div class="site-footer__brand">
                    <a href="<?php echo esc_url( get_home_url() ); ?>" title="<?php bloginfo( 'name' ); ?>" class="site-footer__logo">
                        <img src="<?php echo $theme_url . 'svg/layout/logo-footer.svg'; ?>" width="504.143" height="93.5002" alt="<?php bloginfo( 'name' ); ?>" loading="lazy"/>
                    </a>
                    <p class="site-footer__tagline">
                        Choose to be a hero!<br>It’s in your blood!
                        <img src="<?php echo $theme_url . 'img/layout/footer-hero.gif'; ?>" width="480" height="480" alt="" class="site-footer__tagline-gif" loading="lazy"/>
                    </p>
                </div>

                <div class="site-footer__columns">
					<?php if ( $menus ) : ?>
                        <div class="site-footer__row">
							<?php foreach ( $menus as $location ) : ?>
                                <div class="site-footer__col">
                                    <h2 class="site-footer__heading"><?php echo esc_html( wp_get_nav_menu_name( $location ) ); ?></h2>
									<?php
									wp_nav_menu( [
										'theme_location' => $location,
										'container'      => false,
										'menu_class'     => 'footer-menu',
										'depth'          => 1,
										'walker'         => new CRL_Walker_Nav_Menu
									] );
									?>
                                </div>
							<?php endforeach; ?>
                        </div>
					<?php endif; ?>

					<?php if ( array_filter( $contact ) ) : ?>
                        <div class="site-footer__row">
                            <div class="site-footer__col">
								<?php if ( $contact['title'] ) : ?>
                                    <h2 class="site-footer__heading"><?php echo esc_html( $contact['title'] ); ?></h2>
								<?php endif; ?>
								<?php if ( $contact['email'] ) : ?>
                                    <a href="mailto:<?php echo esc_attr( antispambot( $contact['email'] ) ); ?>" class="site-footer__contact">
                                        <span class="site-footer__contact-icon"><?php CRL_SVG::svg( 'layout/icon-envelope' ); ?></span>
										<?php echo esc_html( antispambot( $contact['email'] ) ); ?>
                                    </a>
								<?php endif; ?>
                            </div>
                            <div class="site-footer__col site-footer__col--end">
								<?php if ( $contact['phone'] ) : ?>
                                    <a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $contact['phone'] ) ); ?>" class="site-footer__contact">
                                        <span class="site-footer__contact-icon"><?php CRL_SVG::svg( 'layout/icon-phone' ); ?></span>
										<?php echo esc_html( $contact['phone'] ); ?>
                                    </a>
								<?php endif; ?>
								<?php if ( $contact['address'] ) : ?>
                                    <p class="site-footer__contact">
                                        <span class="site-footer__contact-icon"><?php CRL_SVG::svg( 'layout/icon-map' ); ?></span>
										<?php echo esc_html( $contact['address'] ); ?>
                                    </p>
								<?php endif; ?>
                            </div>
                        </div>
					<?php endif; ?>
                </div>
            </div>

            <div class="site-footer__bottom">
				<?php if ( $socials ) : ?>
                    <ul class="site-footer__socials">
						<?php foreach ( $socials as $network => [ $url, $label, $width, $height ] ) : ?>
                            <li>
                                <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener" title="<?php echo esc_attr( $label ); ?>">
                                    <img src="<?php echo $theme_url . "svg/layout/$network.svg"; ?>" width="<?php echo $width; ?>" height="<?php echo $height; ?>" alt="<?php echo esc_attr( $label ); ?>" loading="lazy"/>
                                </a>
                            </li>
						<?php endforeach; ?>
                    </ul>
				<?php endif; ?>
                <p class="site-footer__copyright">© Choose Life. All Rights Reserved.</p>
            </div>
        </div>
    </div>
</footer>
