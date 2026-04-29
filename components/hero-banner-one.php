<?php
/**
 * Template part for displaying hero banner one.
 *
 * @package RRTV\TemplateParts
 */

defined( 'ABSPATH' ) || exit;

$rrtv_background_image_id = get_field( 'hero_banner_background_image' );
$rrtv_image_id            = get_field( 'hero_banner_image' );
$rrtv_title               = get_field( 'hero_banner_title' );
$rrtv_subtitle            = get_field( 'hero_banner_subtitle' );
?>

<?php if ( $rrtv_background_image_id || $rrtv_image_id || $rrtv_title || $rrtv_subtitle ) : ?>
	<section class="hero-banner-one">
		<?php
		if ( $rrtv_background_image_id ) {
			echo wp_get_attachment_image(
				$rrtv_background_image_id,
				'full',
				false,
				array(
					'class'         => 'hero-banner-one__background-image skip-lazy',
					'fetchpriority' => 'high',
				)
			);
		}
		?>

		<div class="container">
			<div class="hero-banner-one__content">
				<?php
				if ( $rrtv_image_id ) {
					echo "<div class='hero-banner-one__image-wrapper'>";
					echo wp_get_attachment_image(
						$rrtv_image_id,
						'full',
						false,
						array(
							'class'         => 'hero-banner-one__image skip-lazy',
							'fetchpriority' => 'high',
						)
					);
					echo '</div>';
				}
				?>

				<?php if ( $rrtv_title ) : ?>
					<div class="hero-banner-one__title"><?php echo wp_kses_post( $rrtv_title ); ?></div>
				<?php endif; ?>

				<?php if ( $rrtv_subtitle ) : ?>
					<div class="hero-banner-one__subtitle"><?php echo wp_kses_post( $rrtv_subtitle ); ?></div>
				<?php endif; ?>
			</div>
			
		</div>
	</section>
<?php endif; ?>