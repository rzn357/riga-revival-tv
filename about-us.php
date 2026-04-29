<?php
/**
 * Template Name: About Us
 *
 * @package RRTV
 */

defined( 'ABSPATH' ) || exit;
?>

<?php get_header(); ?>

<main id="main" tabindex="-1" class="main-content">
	
	<?php get_template_part( 'components/about-company' ); ?>

	<?php get_template_part( 'components/our-team' ); ?>

</main>

<?php get_footer(); ?>