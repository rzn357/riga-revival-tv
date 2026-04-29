<?php
/**
 * Template part for displaying features.
 *
 * @package RRTV\TemplateParts
 */

defined( 'ABSPATH' ) || exit;

$rrtv_features = get_field( 'features_items' );
?>

<?php if ( $rrtv_features ) : ?>
	<section class="features">
		<div class="container">	
			<div class="features__items">
				<?php foreach ( $rrtv_features as $rrtv_feature ) : ?>
					<div class="features__item">
						<?php if ( ! empty( $rrtv_feature['icon'] ) ) : ?>
							<div class="features__item__icon-wrapper">
								<?php echo wp_get_attachment_image( $rrtv_feature['icon'], 'full', false, array( 'class' => 'features__item__icon' ) ); ?>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $rrtv_feature['title'] ) ) : ?>
							<div class="features__item__title"><?php echo wp_kses_post( $rrtv_feature['title'] ); ?></div>
						<?php endif; ?>

						<?php if ( ! empty( $rrtv_feature['description'] ) ) : ?>
							<div class="features__item__description"><?php echo wp_kses_post( $rrtv_feature['description'] ); ?></div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>