<?php
/**
 * Template part for displaying map image section.
 *
 * @package RRTV\TemplateParts
 */

defined( 'ABSPATH' ) || exit;

$rrtv_features = get_field( 'map_features' );
$rrtv_image_id = get_field( 'map_image' );
?>

<?php if ( $rrtv_image_id || $rrtv_features ) : ?>
	<section class="map-image">
			<div class="container">

				<?php if ( $rrtv_features ) : ?>
					<div class="map-image__features">
						<?php foreach ( $rrtv_features as $rrtv_feature ) : ?>
							<div class="map-image__feature">
								<?php if ( $rrtv_feature['title'] ) : ?>
									<div class="map-image__feature-title">
										<?php echo wp_kses_post( $rrtv_feature['title'] ); ?>
									</div>
								<?php endif; ?>

								<?php if ( $rrtv_feature['description'] ) : ?>
									<div class="map-image__feature-description">
										<?php echo wp_kses_post( $rrtv_feature['description'] ); ?>
									</div>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( $rrtv_image_id ) : ?>
					<?php echo wp_get_attachment_image( $rrtv_image_id, 'full', false, array( 'class' => 'map-image__image' ) ); ?>
				<?php endif; ?>
			</div>
	</section>
<?php endif; ?>