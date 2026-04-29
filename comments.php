<?php

/**
 * The template for displaying comments.
 *
 * @package RRTV
 */

defined('ABSPATH') || exit;

if (post_password_required()) {
	return;
}
?>

<section id="comments" class="comments-area">
	<?php if (have_comments()) : ?>
		<h2 class="comments-title">
			<?php
			if (1 === (int) get_comments_number()) {
				esc_html_e('One comment', 'riga-revival-tv');
			} else {
				echo esc_html(
					number_format_i18n(get_comments_number()) . ' ' . _n('comment', 'comments', get_comments_number(), 'riga-revival-tv')
				);
			}
			?>
		</h2>

		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 48,
				)
			);
			?>
		</ol>

		<?php the_comments_navigation(); ?>

		<?php if (! comments_open() && get_comments_number()) : ?>
			<p class="no-comments"><?php esc_html_e('Comments are closed.', 'riga-revival-tv'); ?></p>
		<?php endif; ?>
	<?php endif; ?>

	<?php
	comment_form(
		array(
			'class_container' => 'comment-respond',
			'class_form'      => 'comment-form',
		)
	);
	?>
</section>