<?php
/**
 * Read up on the WP Template Hierarchy for
 * when this file is used
 * */

get_header();
?>
    <div class="container py-5">
        <div class="row">
            <div class="col-12 col-xl-10 offset-xl-1">
                <h1><?php the_title(); ?></h1>
                <div class="page-content">
					<?php
					if ( have_posts() ) :
						while ( have_posts() ) : the_post();
							the_content();
						endwhile;
					endif;
					?>
                </div>
            </div>
        </div>
    </div>
<?php

get_footer();