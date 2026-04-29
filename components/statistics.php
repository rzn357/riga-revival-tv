<?php
/**
 * Template part for displaying "statistics" section.
 *
 * @package RRTV\TemplateParts
 */

defined( 'ABSPATH' ) || exit;

$rrtv_items = get_field( 'statistics_items' );
?>

<?php if ( $rrtv_items ) : ?>
	<section class="statistics stats-animation">
		<div class="container">
			<div class="statistics__inner">
				<?php foreach ( $rrtv_items as $rrtv_item ) : ?>
					<div class="statistics__item stats-animation__item">
						<?php echo wp_get_attachment_image( $rrtv_item['icon'], 'full', false, array( 'class' => 'statistics__item-icon' ) ); ?>
						<div class="statistics__item-content">
							<div class="statistics__item-value stats-animation__value"><?php echo esc_html( $rrtv_item['value'] ); ?></div>
							<div class="statistics__item-title stats-animation__title"><?php echo wp_kses_post( $rrtv_item['title'] ); ?></div>
						</div>
					</div>
				<?php endforeach; ?>	
			</div>
		</div>
	</section>
<?php endif; ?>