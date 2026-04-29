<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package RRTV
 */

defined( 'ABSPATH' ) || exit;
?>

<?php get_header(); ?>

<main id="main" tabindex="-1" class="main-content">
	<h1><?php bloginfo( 'name' ); ?></h1>
	<p><?php bloginfo( 'description' ); ?></p>
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			?>
			<article>
				<h2><?php the_title(); ?></h2>
				<div><?php the_content(); ?></div>
			</article>
			<?php
		endwhile;
	endif;
	?>
</main>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
