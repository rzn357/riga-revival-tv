<?php
/**
 * Template part for displaying sections with only title and image.
 *
 * @package RRTV\TemplateParts
 */

defined( 'ABSPATH' ) || exit;

$rrtv_title    = get_field( 'map_title' );
$rrtv_image_id = get_field( 'map_image' );

?>

<?php if ( $rrtv_title || $rrtv_image_id ) : ?>
	<section class="title-image">

		<div class="container">
			
			<?php if ( $rrtv_title ) : ?>
				<div class="title-image__title"><?php echo wp_kses_post( $rrtv_title ); ?></div>
			<?php endif; ?>

			<?php if ( $rrtv_image_id ) : ?>
				<div class="title-image__image-wrapper">
					<?php echo wp_get_attachment_image( $rrtv_image_id, 'full', false, array( 'class' => 'title-image__image' ) ); ?>
				</div>
			<?php endif; ?>	

		</div>
		
	</section>
<?php endif; ?>