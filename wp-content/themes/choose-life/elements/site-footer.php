<footer class="site-footer">
	<div class="container">
        <a href="<?php echo get_home_url(); ?>" title="<?php bloginfo( 'name' ); ?>" class="site-footer__logo">
            <img src="<?php echo get_template_directory_uri() . '/assets/svg/logo.svg'; ?>" alt="<?php echo get_bloginfo('name'); ?>>"/>
        </a>
        <?php
        if ( has_nav_menu( 'footer-menu' ) ) :
            wp_nav_menu( [
                'theme_location'  => 'footer-menu',
                'container'       => 'div',
                'container_class' => 'site-footer__menu',
                'depth'           => 1,
                'menu'            => 'Footer Menu',
                'walker'          => new CRL_Walker_Nav_Menu
            ] );
        endif;
        ?>
		<div class="site-footer__sign">
			<span>© <?php echo date('Y'); ?> Choose Life. All rights reserved.</span>
		</div>
	</div>
</footer>