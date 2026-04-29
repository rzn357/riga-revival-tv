<?php
/**
 * Template part for displaying program banner section.
 *
 * @package RRTV\TemplateParts
 */

// Prevent direct access.
defined( 'ABSPATH' ) || exit;

$rrtv_current_program_data = $args['current_program_data'] ?? array();

if ( empty( $rrtv_current_program_data ) ) {
	return;
}
?>

<section class="program-banner runtime-clampify-disable">
	<div class="program-banner__image-wrapper">
		<?php if ( $rrtv_current_program_data['thumbnail'] ) : ?>
			<?php
			echo wp_get_attachment_image(
				$rrtv_current_program_data['thumbnail'],
				'full',
				false,
				array(
					'class'         => 'program-banner__image skip-lazy',
					'fetchpriority' => 'high',
				)
			);
			?>
		<?php else : ?>
			<img alt="Placeholder" src="<?php echo esc_url( RRTV_THEME_DIR_URL . '/assets/img/placeholder.png' ); ?>" class="program-banner__image skip-lazy" fetchpriority="high" />
		<?php endif; ?>	
	</div>

	<div class="container">
		<div class="program-banner__info">
			<?php if ( $rrtv_current_program_data['title'] ) : ?>
				<div class="program-banner__title"><?php echo wp_kses_post( $rrtv_current_program_data['title'] ); ?></div>
			<?php endif; ?>

			<?php if ( $rrtv_current_program_data['description'] ) : ?>
				<div class="program-banner__description"><?php echo wp_kses_post( $rrtv_current_program_data['description'] ); ?></div>
			<?php endif; ?>
		</div>
	</div>
</section>
