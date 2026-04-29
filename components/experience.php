<?php
/**
 * Template part for displaying experience section.
 *
 * @package RRTV\TemplateParts
 */

defined( 'ABSPATH' ) || exit;

$rrtv_image_id    = get_field( 'experience_image' );
$rrtv_title       = get_field( 'experience_title' );
$rrtv_description = get_field( 'experience_description' );
$rrtv_button      = get_field( 'experience_button' );
?>

<?php if ( $rrtv_image_id || $rrtv_title || $rrtv_description ) : ?>
	<section class="experience">
		<div class="container">

			<div class="experience__row">
				
					<div class="experience__image-wrapper">
						<?php if ( $rrtv_image_id ) : ?>
							<?php echo wp_get_attachment_image( $rrtv_image_id, 'full', false, array( 'class' => 'experience__image' ) ); ?>
						<?php endif; ?>
					</div>
				
					<div class="experience__content">
						<div class="experience__title"><?php echo wp_kses_post( $rrtv_title ); ?></div>

						<?php if ( $rrtv_description ) : ?>
							<div class="experience__description"><?php echo wp_kses_post( $rrtv_description ); ?></div>
						<?php endif; ?>

						<?php if ( $rrtv_button ) : ?>
							<a href="<?php echo esc_url( $rrtv_button['url'] ); ?>" class="experience__button btn btn--primary" <?php echo esc_html( $rrtv_button['target'] ? 'target="' . esc_attr( $rrtv_button['target'] ) . '"' : '' ); ?>>
								<?php echo esc_html( $rrtv_button['title'] ); ?>
							</a>
						<?php endif; ?>
					</div>
					
			</div>
			
		</div>
	</section>
<?php endif; ?>