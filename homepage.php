<?php
/**
 * Template Name: Homepage
 *
 * @package RRTV
 */

defined( 'ABSPATH' ) || exit;
?>
<?php get_header(); ?>

<main id="main" tabindex="-1" class="main-content">

	<?php get_template_part( 'components/hero-banner-one' ); ?>

	<?php get_template_part( 'components/experience' ); ?>

	<?php get_template_part( 'components/our-videos' ); ?>

	<?php get_template_part( 'components/statistics' ); ?>

	<?php get_template_part( 'components/title-image' ); ?>

</main>

<?php get_footer(); ?>