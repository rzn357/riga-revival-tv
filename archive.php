<?php

/**
 * The template for displaying archive pages.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package RRTV
 */

defined('ABSPATH') || exit;
?>

<?php get_header(); ?>

<main id="main" tabindex="-1" class="main-content">
	<div class="container">
		<header class="archive-header">
			<?php the_archive_title('<h1 class="archive-title">', '</h1>'); ?>
			<?php the_archive_description('<div class="archive-description">', '</div>'); ?>
		</header>

		<?php
		if (have_posts()) :
			while (have_posts()) :
				the_post();
		?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<?php the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '">', '</a></h2>'); ?>
						<div class="entry-meta">
							<time datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>">
								<?php echo esc_html(get_the_date()); ?>
							</time>
						</div>
					</header>

					<?php if (has_post_thumbnail()) : ?>
						<div class="entry-thumbnail">
							<a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
								<?php the_post_thumbnail('medium_large'); ?>
							</a>
						</div>
					<?php endif; ?>

					<div class="entry-summary">
						<?php the_excerpt(); ?>
					</div>
				</article>
			<?php
			endwhile;

			the_posts_pagination(
				array(
					'mid_size'  => 2,
					'prev_text' => esc_html__('Previous', 'riga-revival-tv'),
					'next_text' => esc_html__('Next', 'riga-revival-tv'),
				)
			);
		else :
			?>
			<p><?php esc_html_e('No posts found.', 'riga-revival-tv'); ?></p>
		<?php
		endif;
		?>
	</div>
</main>

<?php get_footer(); ?>