<?php
/**
 * Template Name: Donate
 *
 * @package RRTV
 */

defined( 'ABSPATH' ) || exit;
?>

<?php get_header(); ?>

<main id="main" tabindex="-1" class="main-content">
	
	<?php get_template_part( 'components/hero-banner-donate' ); ?>

	<?php get_template_part( 'components/video-tutorial' ); ?>
	
	<?php get_template_part( 'components/map-image' ); ?>

</main>

<?php get_footer(); ?>