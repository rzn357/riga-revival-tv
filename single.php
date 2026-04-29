<?php

/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package RRTV
 */

defined('ABSPATH') || exit;
?>

<?php get_header(); ?>

<main id="main" tabindex="-1" class="main-content">
	<div class="container">
		<?php
		if (have_posts()) :
			while (have_posts()) :
				the_post();
		?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<?php the_title('<h1 class="entry-title">', '</h1>'); ?>

						<div class="entry-meta">
							<time datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>">
								<?php echo esc_html(get_the_date()); ?>
							</time>
							<span class="entry-meta-separator"> • </span>
							<span class="entry-author">
								<?php
								printf(
									/* translators: %s: Author display name. */
									esc_html__('By %s', 'riga-revival-tv'),
									esc_html(get_the_author())
								);
								?>
							</span>
						</div>
					</header>

					<?php if (has_post_thumbnail()) : ?>
						<div class="entry-thumbnail">
							<?php the_post_thumbnail('large'); ?>
						</div>
					<?php endif; ?>

					<div class="entry-content">
						<?php
						the_content();

						wp_link_pages(
							array(
								'before' => '<nav class="page-links" aria-label="' . esc_attr__('Post pages', 'riga-revival-tv') . '">' . esc_html__('Pages:', 'riga-revival-tv'),
								'after'  => '</nav>',
							)
						);
						?>
					</div>
				</article>

				<nav class="post-navigation" aria-label="<?php esc_attr_e('Post navigation', 'riga-revival-tv'); ?>">
					<?php
					the_post_navigation(
						array(
							'prev_text' => esc_html__('Previous: %title', 'riga-revival-tv'),
							'next_text' => esc_html__('Next: %title', 'riga-revival-tv'),
						)
					);
					?>
				</nav>

				<?php
				if (comments_open() || get_comments_number()) {
					comments_template();
				}
				?>
			<?php
			endwhile;
		else :
			?>
			<p><?php esc_html_e('Sorry, no post was found.', 'riga-revival-tv'); ?></p>
		<?php
		endif;
		?>
	</div>
</main>

<?php get_footer(); ?>