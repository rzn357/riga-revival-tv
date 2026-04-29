<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package RRTV
 */

defined( 'ABSPATH' ) || exit;
?>

<?php get_header(); ?>

<main id="main" tabindex="-1" class="main-content">
	<div class="container">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
					</header>

					<?php if ( has_post_thumbnail() ) : ?>
						<div class="entry-thumbnail">
							<?php the_post_thumbnail( 'large' ); ?>
						</div>
					<?php endif; ?>

					<div class="entry-content">
						<?php
						the_content();

						wp_link_pages(
							array(
								'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Page content navigation', 'riga-revival-tv' ) . '">' . esc_html__( 'Pages:', 'riga-revival-tv' ),
								'after'  => '</nav>',
							)
						);

						edit_post_link(
							esc_html__( 'Edit this page', 'riga-revival-tv' ),
							'<p class="edit-link">',
							'</p>'
						);
						?>
					</div>
				</article>

				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
				<?php
			endwhile;
		else :
			?>
			<p><?php esc_html_e( 'Sorry, no page was found.', 'riga-revival-tv' ); ?></p>
			<?php
		endif;
		?>
	</div>
</main>

<?php get_footer(); ?>
