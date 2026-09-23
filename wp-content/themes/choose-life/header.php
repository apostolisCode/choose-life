<?php
/**
 * Header file common to all
 * templates
 *
 */
?><!doctype html>
<html class="site no-js" <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="theme-color" content="#1C488B"/>

    <title><?php wp_title(); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600&family=Manrope:wght@400;500;700&family=Noto+Sans:ital,wght@0,400..600;1,400..600&display=swap" rel="stylesheet">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'site__body' ); ?>>

<?php get_template_part( 'elements/site-header' ); ?>
<div class="site__wrapper">
