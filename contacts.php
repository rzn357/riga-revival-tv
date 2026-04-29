<?php
/**
 * Template Name: Contacts
 *
 * @package RRTV
 */

defined( 'ABSPATH' ) || exit;
?>

<?php get_header(); ?>

<main id="main" tabindex="-1" class="main-content">
	
	<?php get_template_part( 'components/hero-banner-one' ); ?>

	<?php get_template_part( 'components/features' ); ?>

	<?php get_template_part( 'components/contacts-info' ); ?>

	<?php get_template_part( 'components/contacts-map' ); ?>

</main>

<?php get_footer(); ?>