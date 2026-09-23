<?php
$svg_url        = get_template_directory_uri() . '/assets/svg/';
$my_account_url = get_field( 'my_account_url', 'options' );
$header_cta     = get_field( 'header_cta', 'options' );

$menu_args = [
	'container'   => false,
	'depth'       => 1,
	'fallback_cb' => false,
	'walker'      => new CRL_Walker_Nav_Menu
];
?>
<header class="site-header">
	<div class="site-header__bar">
		<div class="site-header__start">
			<?php get_template_part( 'elements/lang-switcher' ); ?>
			<?php wp_nav_menu( array_merge( $menu_args, [ 'theme_location' => 'header-menu-left', 'menu_class' => 'nav-menu', 'container' => 'nav', 'container_class' => 'site-header__nav' ] ) ); ?>
		</div>

		<a href="<?php echo esc_url( get_home_url() ); ?>" title="<?php bloginfo( 'name' ); ?>" class="site-header__logo">
			<img src="<?php echo $svg_url . 'layout/logo-header.svg'; ?>" width="237.201" height="44" alt="<?php bloginfo( 'name' ); ?>">
		</a>

		<div class="site-header__end">
			<?php wp_nav_menu( array_merge( $menu_args, [ 'theme_location' => 'header-menu-right', 'menu_class' => 'nav-menu', 'container' => 'nav', 'container_class' => 'site-header__nav' ] ) ); ?>
			<div class="account-links">
				<a href="<?php echo esc_url( $my_account_url ); ?>" title="<?php _e( 'My account', 'choose-life' ); ?>" class="account-links__icon">
					<img src="<?php echo $svg_url . 'layout/user.svg'; ?>" width="21.5" height="21.5" alt="<?php _e( 'My account', 'choose-life' ); ?>"/>
				</a>
				<div id="account-menu-links"></div>
			</div>
			<?php if ( ! empty( $header_cta['url'] ) ) : ?>
				<a href="<?php echo esc_url( $header_cta['url'] ); ?>" class="cl-btn cl-btn--primary cl-btn--sm site-header__cta"<?php echo $header_cta['target'] ? ' target="' . esc_attr( $header_cta['target'] ) . '" rel="noopener"' : ''; ?>><?php echo esc_html( $header_cta['title'] ); ?></a>
			<?php endif; ?>
			<button type="button" class="site-header__toggle" aria-expanded="false" aria-controls="site-header-panel" aria-label="<?php esc_attr_e( 'Menu', 'choose-life' ); ?>">
				<span></span>
			</button>
		</div>
	</div>

	<div class="site-header__panel" id="site-header-panel" hidden>
		<?php
		wp_nav_menu( array_merge( $menu_args, [ 'theme_location' => 'header-menu-left', 'menu_class' => 'nav-menu' ] ) );
		wp_nav_menu( array_merge( $menu_args, [ 'theme_location' => 'header-menu-right', 'menu_class' => 'nav-menu' ] ) );
		?>
		<?php if ( ! empty( $header_cta['url'] ) ) : ?>
			<a href="<?php echo esc_url( $header_cta['url'] ); ?>" class="cl-btn cl-btn--primary cl-btn--block"<?php echo $header_cta['target'] ? ' target="' . esc_attr( $header_cta['target'] ) . '" rel="noopener"' : ''; ?>><?php echo esc_html( $header_cta['title'] ); ?></a>
		<?php endif; ?>
	</div>
</header>
