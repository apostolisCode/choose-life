<?php
/**
 * Newsletter card (Theme Options → Newsletter), shared by all templates:
 * get_template_part( 'elements/sections/newsletter' ).
 * Its form is Contact Form 7 (fields in elements/cf7-newsletter.php).
 */
get_template_part( 'elements/sections/feature-card', null, [
	'text'         => get_field( 'newsletter_text', 'options' ),
	'title'        => get_field( 'newsletter_title', 'options' ),
	'title_accent' => get_field( 'newsletter_title_accent', 'options' ),
	'form'         => get_field( 'newsletter_form', 'options' ),
	'image'        => get_field( 'newsletter_image', 'options' ),
	'modifier'     => 'dark',
] );
