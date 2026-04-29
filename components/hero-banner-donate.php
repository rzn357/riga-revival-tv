<?php
/**
 * Template part for displaying hero banner two.
 *
 * @package RRTV\TemplateParts
 */

defined( 'ABSPATH' ) || exit;

$rrtv_background_image_id = get_field( 'hero_banner_two_background_image' );
$rrtv_title               = get_field( 'hero_banner_two_title' );
$rrtv_subtitle            = get_field( 'hero_banner_two_subtitle' );
$rrtv_buttons             = get_field( 'hero_banner_two_buttons' );
?>

<?php if ( $rrtv_background_image_id || $rrtv_title || $rrtv_subtitle ) : ?>
	<section class="hero-banner-donate">
		<div class="hero-banner-donate__top">
			<?php
			if ( $rrtv_background_image_id ) {
				echo wp_get_attachment_image(
					$rrtv_background_image_id,
					'full',
					false,
					array(
						'class'         => 'hero-banner-donate__background-image skip-lazy',
						'fetchpriority' => 'high',
					)
				);
			}
			?>

			<div class="container">
				<div class="hero-banner-donate__content">
					<?php if ( $rrtv_title ) : ?>
						<div class="hero-banner-donate__title"><?php echo wp_kses_post( $rrtv_title ); ?></div>
					<?php endif; ?>

					<?php if ( $rrtv_subtitle ) : ?>
						<div class="hero-banner-donate__subtitle"><?php echo wp_kses_post( $rrtv_subtitle ); ?></div>
					<?php endif; ?>
				</div>			
			</div>
		</div>
		<?php if ( $rrtv_buttons ) : ?>
			<div class="hero-banner-donate__bottom">
				<div class="container">
					<div class="hero-banner-donate__buttons">
						<?php foreach ( $rrtv_buttons as $rrtv_index => $rrtv_button ) : ?>
							<?php if ( $rrtv_button['form_shortcode'] ) : ?>
								<div class="hero-banner-donate__button-wrapper">
									<?php echo do_shortcode( $rrtv_button['form_shortcode'] ); ?>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</section>
<?php endif; ?>