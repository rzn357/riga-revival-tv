<?php
/**
 * Template part for displaying "Video tutorial" section.
 *
 * @package RRTV\TemplateParts
 */

defined( 'ABSPATH' ) || exit;

$rrtv_video        = get_field( 'video' );
$rrtv_video_poster = get_field( 'video_poster' );
?>

<?php if ( $rrtv_video ) : ?>
	<section class="video-tutorial">
		<div class="container video-tutorial__container">
			<div class="video-tutorial__video runtime-clampify-disable-full">
				<?php echo do_shortcode( '[video src="' . esc_url( $rrtv_video ) . '" preload="metadata" poster="' . esc_url( $rrtv_video_poster ) . '"]' ); ?>
			</div>			
		</div>
	</section>
<?php endif; ?>