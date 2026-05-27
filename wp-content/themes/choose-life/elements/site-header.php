<?php
    $svg_url = get_template_directory_uri() . '/assets/svg/';
    $my_account_url = get_field('my_account_url', 'options');
?>
<header class="site-header">
	<div class="container">
		<div class="site-header__wrapper">
			<a href="<?php echo get_home_url(); ?>" title="<?php bloginfo( 'name' ); ?>" class="site-header__logo">
                <img src="<?php echo get_template_directory_uri() . '/assets/svg/logo.svg'; ?>" alt="logo">
			</a>
            <?php
            if ( has_nav_menu( 'main-menu' ) ) :
                wp_nav_menu( [
                    'theme_location'  => 'main-menu',
                    'container'       => 'div',
                    'container_class' => 'site-header__menu',
                    'depth'           => 1,
                    'menu'            => 'Main Menu',
                    'walker'          => new CRL_Walker_Nav_Menu
                ] );
            endif;
            ?>
            <div class="account-links">
                <a href="<?php echo $my_account_url; ?>" title="<?php _e('My account', 'choose-life'); ?>">
                    <img src="<?php echo $svg_url . "user.svg"; ?>" alt="<?php _e('My account', 'choose-life'); ?>"/>
                </a>
                <div id="account-menu-links"></div>
            </div>
		</div>
	</div>
</header>